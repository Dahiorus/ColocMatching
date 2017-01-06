<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Form\Type\User\UserType;
use ColocMatching\CoreBundle\Controller\Rest\RestDataResponse;
use ColocMatching\CoreBundle\Controller\Rest\RestListResponse;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * REST controller for resource /users
 *
 * @author brondon.ung
 */
class UserController extends Controller {
	
	/**
	 * Get users or fields with pagination
	 *
	 * @Rest\Get("", name="rest_get_users")
	 * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+", default="1")
	 * @Rest\QueryParam(name="limit", nullable=true, description="The number of results to return", requirements="\d+", default="20")
	 * @Rest\QueryParam(name="orderBy", nullable=true, description="The name of the attribute to order the results", default="id")
	 * @Rest\QueryParam(name="sort", nullable=true, description="'asc' if ascending order, 'desc' if descending order", requirements="(asc|desc)", default="asc")
	 * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return for each result")
	 * @ApiDoc(
	 *   section="Users",
	 *   description="Get users or fields with pagination",
	 *   resource=true,
	 *   statusCodes={
	 * 	   200="OK",
	 *     206="Partial content"
	 *   },
	 *   output={ "class"=User::class, "collection"=true }
	 * )
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function getUsersAction(Request $request) {
		$page = $request->query->get('page', 1);
		$limit = $request->query->get('limit', 20);
		$orderBy = $request->query->get('orderBy', 'id');
		$sort = $request->query->get('sort', 'asc');
		$fields = $request->query->get('fields', null);
		
		/** @var array */
		$users = [];
		/** @var UserManager */
		$manager = $this->get('coloc_matching.core.user_manager');
		
		if ($fields) {
			$users = $manager->getFields(explode(',', $fields), $page, $limit, $orderBy, $sort);
		} else {
			$users = $manager->getAll($page, $limit, $orderBy, $sort);
		}
		
		$restList = new RestListResponse($users, '/rest/users');
		$restList
			->setTotal($manager->countAll())
			->setStart(($page-1) * $limit)
			->setOrderBy($orderBy)
			->setSort($sort);
		
		/** @var int */
		$codeStatus = ($restList->getSize() < $restList->getTotal()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK;
		
		return new JsonResponse(
			$this->get('jms_serializer')->serialize($restList, 'json'),
			$codeStatus, [], true);
	}
	
	/**
	 * Get a user or its fields by id
	 *
	 * @Rest\Get("/{id}", name="rest_get_user")
	 * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return")
	 * @ApiDoc(
	 *   section="Users",
	 *   description="Get a user or its fields by id",
	 *   requirements={
	 *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The user id" }
	 *   },
	 *   output="ColocMatching\CoreBundle\Entity\User\User",
	 *   statusCodes={
	 *     200="OK",
	 *     404="User not found"
	 * })
	 *
	 * @param int $id
	 * @return JsonResponse
	 */
	public function getUserAction(int $id, Request $request) {
		/** @var array */
		$fields = $request->query->get('fields', null);
		/** @var UserManager */
		$manager = $this->get('coloc_matching.core.user_manager');
		
		/** @var User*/
		$user = (!$fields) ? $manager->getById($id) : $manager->getFieldsById($id, explode(',', $fields));
		
		if (!$user) {
			throw new NotFoundHttpException("User not found with the Id $id");
		}
		
		$restData = new RestDataResponse($user, "/rest/users/$id");
		
		return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'),
			Response::HTTP_OK, [], true);
	}
	
	/**
	 * Create new User
	 *
	 * @Rest\Post("", name="rest_create_user")
	 * @Rest\RequestParam(name="user", requirements="array", description="The user data to post", nullable=false)
	 * @ApiDoc(
	 *   section="Users",
	 *   description="Create a new user",
	 *   input={ "class"=UserType::class },
	 *   statusCodes={
	 *     201="Created",
	 *     400="Invalid form"
	 *   },
	 *   responseMap={
	 *     201={ "class"=User::class },
	 *     400={ "class"=UserType::class, "form_errors"=true, "name"="" }
	 * })
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function createUserAction(Request $request) {
		/** @var array */
		$postData = $request->request->get('user');
		
		try {
			/** @var User */
			$user = $this->get('coloc_matching.core.user_manager')->create($postData);

			$restData = new RestDataResponse($user, '/rest/users/' . $user->getId());
			$responseData = $this->get('jms_serializer')->serialize($restData, 'json');
			$statusCode = Response::HTTP_CREATED;
		} catch (InvalidFormDataException $e) {
			$responseData = $e->toJSON();
			$statusCode = Response::HTTP_BAD_REQUEST;
		}
		
		return new JsonResponse($responseData, $statusCode, [], true);
	}
	
	
	/**
	 * Update an existing user
	 *
	 * @Rest\Put("/{id}", name="rest_put_user")
	 * @Rest\RequestParam(name="user", requirements="array", description="The user data to put", nullable=false)
	 * @ApiDoc(
	 *   section="Users",
	 *   description="Update an existing user",
	 *   requirements={
	 *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The user id" }
	 *   },
	 *   input={ "class"=UserType::class },
	 *   statusCodes={
	 *     200="OK",
	 *     400="Invalid form",
	 *     404="User not found"
	 *   },
	 *   responseMap={
	 *     200={ "class"=User::class },
	 *     400={ "class"=UserType::class, "form_errors"=true, "name"="" }
	 * })
	 *
	 * @param int $id
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function updateUserAction(int $id, Request $request) {
		/** @var UserManager */
		$manager = $this->get('coloc_matching.core.user_manager');
		/** @var User */
		$user = $manager->getById($id);
		
		if (!$user) {
			throw new NotFoundHttpException("User not found with the Id $id");
		}
		
		/** @var array */
		$putData = $request->request->get('user');
		
		try {
			/** @var User */
			$user = $this->get('coloc_matching.core.user_manager')->update($user, $putData);
				
			$restData = new RestDataResponse($user, "/rest/users/$id");
			$responseData = $this->get('jms_serializer')->serialize($restData, 'json');
			$statusCode = Response::HTTP_OK;
		} catch (InvalidFormDataException $e) {
			$responseData = $e->toJSON();
			$statusCode = Response::HTTP_BAD_REQUEST;
		}
		
		return new JsonResponse($responseData, $statusCode, [], true);
	}
	
	
	/**
	 * Patch an existing user
	 *
	 * @Rest\Patch("/{id}", name="rest_patch_user")
	 * @Rest\RequestParam(name="user", requirements="array", description="The user data to patch", nullable=false)
	 * @ApiDoc(
	 *   section="Users",
	 *   description="Patch an existing user",
	 *   requirements={
	 *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The user id" }
	 *   },
	 *   input={ "class"=UserType::class },
	 *   statusCodes={
	 *     200="OK",
	 *     400="Invalid form",
	 *     404="User not found"
	 *   },
	 *   responseMap={
	 *     200={ "class"=User::class },
	 *     400={ "class"=UserType::class, "form_errors"=true, "name"="" }
	 *   })
	 *
	 * @param int $id
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function patchUserAction(int $id, Request $request) {
		/** @var UserManager */
		$manager = $this->get('coloc_matching.core.user_manager');
		/** @var User */
		$user = $manager->getById($id);
		
		if (!$user) {
			throw new NotFoundHttpException("User not found with the Id $id");
		}
		
		/** @var array */
		$patchData = $request->request->get('user');
		
		try {
			/** @var User */
			$user = $this->get('coloc_matching.core.user_manager')->partialUpdate($user, $patchData);
		
			$restData = new RestDataResponse($user, '/rest/users/' . $user->getId());
			$responseData = $this->get('jms_serializer')->serialize($restData, 'json');
			$statusCode = Response::HTTP_OK;
		} catch (InvalidFormDataException $e) {
			$responseData = $e->toJSON();
			$statusCode = Response::HTTP_BAD_REQUEST;
		}
		
		return new JsonResponse($responseData, $statusCode, [], true);
	}
	
	
	/**
	 * Patch an existing user
	 *
	 * @Rest\Delete("/{id}", name="rest_delete_user")
	 * @ApiDoc(
	 *   section="Users",
	 *   description="Delete an existing user",
	 *   requirements={
	 *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The user id" }
	 *   },
	 *   statusCodes={
	 *     200="OK"
	 * })
	 *
	 * @param int $id
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function deleteUserAction(int $id) {
		/** @var UserManager */
		$manager = $this->get('coloc_matching.core.user_manager');
		/** @var User */
		$user = $manager->getById($id);
		
		if ($user) {
			$manager->delete($user);
		}
		
		return new JsonResponse('', Response::HTTP_OK, [], true);
	}
	
}
