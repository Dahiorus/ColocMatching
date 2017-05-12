<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\AuthenticationControllerInterface;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Form\Type\Security\LoginType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use FOS\RestBundle\Controller\Annotations\Route;

/**
 * REST Controller for authenticating User in the API
 *
 * @Route("/auth-tokens/")
 *
 * @author brondon.ung
 */
class AuthenticationController extends Controller implements AuthenticationControllerInterface {


    /**
     * @Rest\Post("", name="rest_create_authtoken")
     * @Rest\RequestParam(name="_username", requirements="string", description="User login", nullable=false)
     * @Rest\RequestParam(name="_password", requirements="string", description="User password", nullable=false)
     *
     * @param Request $request
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function postAuthTokenAction(Request $request) {
        /** @var string */
        $_username = $request->request->get('_username');
        $_password = $request->request->get('_password');

        $this->get('logger')->info(sprintf("Request an authentication token [_username: '%s']", $_username),
            [ 'request' => $request]);

        try {
            /** @var User */
            $user = $this->processCredentials($_username, $_password);

            if (!$user->isEnabled()) {
                $this->get('logger')->error(sprintf("Forbidden access for the User [_username: '%s']", $_username),
                    [ 'request' => $request, 'user' => $user]);

                throw new AccessDeniedHttpException("Forbidden access for the user '$_username'");
            }

            $token = $this->get('lexik_jwt_authentication.encoder')->encode([ 'username' => $user->getUsername()]);

            $this->get('logger')->info(sprintf("Authentication token requested [_username: '%s']", $_username));

            return new JsonResponse(
                array (
                    "token" => $token,
                    "user" => array (
                        "id" => $user->getId(),
                        "username" => $user->getUsername(),
                        "name" => sprintf("%s %s", $user->getFirstname(), $user->getLastname()),
                        "type" => $user->getType())), Response::HTTP_CREATED);
        }
        catch (InvalidFormDataException $e) {
            $this->get('logger')->error(sprintf("Incomplete login information [_username: '%s']", $_username),
                [ "request" => $request]);

            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ ], true);
        }
    }


    /**
     * Process the credentials and return a User
     *
     * @param string $_username
     * @param string $_password
     * @throws InvalidFormDataException
     * @throws UserNotFoundException
     * @return User
     */
    private function processCredentials(string $_username, string $_password): User {
        /** @var Form */
        $form = $this->createForm(LoginType::class);

        $this->get('logger')->info(sprintf("Process login information [_username: '%s']", $_username));

        if (!$form->submit(array ('_username' => $_username, '_password' => $_password))->isValid()) {
            throw new InvalidFormDataException("Invalid submitted data in the login form", $form->getErrors(true, true));
        }

        /** @var User */
        $user = $this->get('coloc_matching.core.user_manager')->findByUsername($_username);

        if (empty($user) || !$this->get('security.password_encoder')->isPasswordValid($user, $_password)) {
            throw new UserNotFoundException("username", $_username);
        }

        $this->get('logger')->info(sprintf("User found [user: %s]", $user), [ 'user' => $user]);

        return $user;
    }

}