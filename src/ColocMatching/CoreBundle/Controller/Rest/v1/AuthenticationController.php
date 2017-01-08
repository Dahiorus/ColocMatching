<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Security\LoginType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * REST Controller for authenticating User in the API
 *
 * @author brondon.ung
 */
class AuthenticationController extends Controller {
	
	/**
	 * Authenticate a User and create an authentication token
	 *
	 * @Rest\Post("", name="rest_post_authtoken")
	 * @Rest\RequestParam(name="_username", requirements="string", description="User login", nullable=false)
	 * @Rest\RequestParam(name="_password", requirements="string", description="User password", nullable=false)
	 * @ApiDoc(
	 *   section="Authentication",
	 *   description="Authenticate a User and create an authentication token",
	 *   resource=true,
	 *   statusCodes={
	 *     201="Created",
	 *     403="Forrbidden access",
	 *     404="User not found"
	 * })
	 * @param Request $request
	 */
	public function postAuthTokenAction(Request $request) {
		/** @var string */
		$_username = $request->request->get('_username');
		$_password = $request->request->get('_password');
		
		/** @var User */
		$user = $this->processCredentials($_username, $_password);
		
		if (!$user->isEnabled()) {
			throw new AccessDeniedHttpException("Forbidden access for '$_username'");
		}
		
		$token = $this->get('lexik_jwt_authentication.encoder')->encode(array (
			'username' => $user->getUsername()
		));
		
		return new JsonResponse(array (
			'success' => 'success',
			'token' => $token
			), Response::HTTP_CREATED);
	}
	
	
	/**
	 * Process the credentials and return a User
	 *
	 * @param string $_username
	 * @param string $_password
	 * @throws InvalidFormDataException
	 * @throws NotFoundHttpException
	 * @return User
	 */
	private function processCredentials(string $_username, string $_password) {
		$form = $this->createForm(LoginType::class);
		
		$form->submit(array (
			'_username' => $_username,
			'_password' => $_password
		));
		
		if (!$form->isValid()) {
			throw new InvalidFormDataException(
				"Invalid submitted data in the credentials form",
				$form->getErrors(true, false));
		}
		
		/** @var User */
		$user = $this->get('coloc_matching.core.user_manager')->getByUsername($_username);
		
		if (!$user || !$this->get('security.password_encoder')->isPasswordValid($user, $_password)) {
			throw new NotFoundHttpException('Bad credentials');
		}
		
		return $user;
	}
	
}