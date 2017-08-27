<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\AuthenticationException;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Security\LoginType;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\AuthenticationControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST Controller for authenticating User in the API
 *
 * @Rest\Route("/auth-tokens/")
 *
 * @author brondon.ung
 */
class AuthenticationController extends RestController implements AuthenticationControllerInterface {

    /**
     * @Rest\Post("", name="rest_create_authtoken")
     * @Rest\RequestParam(name="_username", requirements="string", description="User login", nullable=false)
     * @Rest\RequestParam(name="_password", requirements="string", description="User password", nullable=false)
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function postAuthTokenAction(Request $request) {
        /** @var string */
        $_username = $request->request->get("_username");
        $_password = $request->request->get("_password");

        $this->get("logger")->info("Requesting an authentication token", array ("_username" => $_username));

        try {
            /** @var User */
            $user = $this->processCredentials($_username, $_password);

            if (!$user->isEnabled()) {
                $this->get("logger")->error("Forbidden access for the user", array ("user" => $user));

                throw new AuthenticationException();
            }

            $token = $this->get("lexik_jwt_authentication.encoder")->encode(array ("username" => $user->getUsername()));

            $this->get("logger")->info("Authentication token requested", array ("_username" => $_username));

            return new JsonResponse(
                array (
                    "token" => $token,
                    "user" => array (
                        "id" => $user->getId(),
                        "username" => $user->getUsername(),
                        "name" => $user->getDisplayName(),
                        "type" => $user->getType())), Response::HTTP_CREATED);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Bad credentials", array ("_username" => $_username));

            return $this->buildBadRequestResponse($e);
        }
    }


    /**
     * Process the credentials and return a User
     *
     * @param string $_username
     * @param string $_password
     *
     * @throws InvalidFormDataException
     * @throws AuthenticationException
     * @return User
     */
    private function processCredentials(string $_username, string $_password) : User {
        /** @var Form */
        $form = $this->createForm(LoginType::class);

        $this->get("logger")->info("Processing login information", array ("_username" => $_username));

        if (!$form->submit(array ("_username" => $_username, "_password" => $_password))->isValid()) {
            throw new InvalidFormDataException("Invalid submitted data in the login form",
                $form->getErrors(true, true));
        }

        $manager = $this->get("coloc_matching.core.user_manager");
        /** @var User $user */
        $user = $manager->findByUsername($_username);

        if (empty($user) || !$this->get("security.password_encoder")->isPasswordValid($user, $_password)) {
            throw new AuthenticationException();
        }

        $this->get("logger")->info("User found", array ("user" => $user));

        $user->setLastLogin(new \DateTime());
        $user = $manager->update($user, array (), false);

        return $user;
    }

}