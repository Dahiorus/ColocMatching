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
use Symfony\Component\Form\Form;

/**
 * REST Controller for authenticating User in the API
 *
 * @author brondon.ung
 */
class AuthenticationController extends Controller {
	
	/**
	 * Authenticate a User and create an authentication token
	 *
	 * @Rest\Post("", name="rest_create_authtoken")
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
		
		$this->get('logger')->info(
			sprintf("Request an authentication token [_username='%s']", $_username),
			['request' => $request]
		);
		
		
		/** @var User */
		$user = $this->processCredentials($_username, $_password);
		
		if (!$user->isEnabled()) {
			$this->get('logger')->error(
				sprintf("Forbidden access for the User [_username='%s']", $_username),
				array (
					'request' => $request,
					'user' => $user
				)
			);
			
			throw new AccessDeniedHttpException("Forbidden access for '$_username'");
		}
		
		$token = $this->get('lexik_jwt_authentication.encoder')->encode(array (
			'username' => $user->getUsername()
		));
		
		$this->get('logger')->info(
			sprintf("Authentication token requested [_username='%s']", $_username)
		);
		
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
		/** @var Form */
		$form = $this->createForm(LoginType::class);
		
		$this->get('logger')->info(
			sprintf("Process login information [_username='%s']", $_username)
		);
		
		$form->submit(array (
			'_username' => $_username,
			'_password' => $_password
		));
		
		if (!$form->isValid()) {
			$this->get('logger')->error(
				sprintf("Incomplete login information [_username='%s']", $_username)
			);
			
			throw new InvalidFormDataException(
				"Invalid submitted data in the login form",
				$form->getErrors(true, false));
		}
		
		/** @var User */
		$user = $this->get('coloc_matching.core.user_manager')->getByUsername($_username);
		
		if (!$user || !$this->get('security.password_encoder')->isPasswordValid($user, $_password)) {
			$this->get('logger')->error(
				sprintf("Incorrect login information [_username='%s']", $_username)
			);
			
			throw new NotFoundHttpException('Bad credentials');
		}
		
		$this->get('logger')->info(
			sprintf("User found [_username='%s']", $_username),
			['user' => $user]
		);
		
		return $user;
	}
	
}