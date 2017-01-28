<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Controller\Rest\RestDataResponse;
use ColocMatching\CoreBundle\Controller\Rest\RestListResponse;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\User\UserType;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results", default="id")
     * @Rest\QueryParam(name="order", nullable=true, description="'asc' if ascending order, 'desc' if descending order", requirements="(asc|desc)", default="asc")
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
     * @throws NotFoundHttpException
     */
    public function getUsersAction(Request $request) {
        $page = $request->query->get('page', RequestConstants::DEFAULT_PAGE);
        $limit = $request->query->get('limit', RequestConstants::DEFAULT_LIMIT);
        $order = $request->query->get('order', RequestConstants::DEFAULT_ORDER);
        $sort = $request->query->get('sort', RequestConstants::DEFAULT_SORT);
        $fields = $request->query->get('fields', null);
        
        $this->get('logger')->info(
            sprintf("Get Users [page: %d | limit: %d | order:'%s' | sort: '%s' | fields: [%s]]", $page, $limit, $order,
                $sort, $fields), [ 'request' => $request]);
        
        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.user_manager');
        
        /** @var AbstractFilter */
        $filter = new UserFilter();
        $filter->setOffset(($page - 1) * $limit)->setSize($limit)->setOrder($order)->setSort($sort);
        
        /** @var array */
        $users = (empty($fields)) ? $manager->list($filter) : $manager->list($filter, explode(",", $fields));
        $restList = new RestListResponse($users, "/rest/users/");
        $restList->setTotal($manager->countAll())->setStart(($page - 1) * $limit)->setOrder($order)->setSort($sort);
        $restList->setRelationLinks($page);
        
        /** @var int */
        $codeStatus = ($restList->getSize() < $restList->getTotal()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK;
        
        $this->get('logger')->info(
            sprintf("Result information : [start: %d | size: %d | total: %d]", $restList->getStart(),
                $restList->getSize(), $restList->getTotal()), [ 'response' => $restList]);
        
        return new JsonResponse($this->get('jms_serializer')->serialize($restList, 'json'), $codeStatus,
            [ "Location" => $request->getUri()], true);
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
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     404="User not found"
     * })
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getUserAction(int $id, Request $request) {
        /** @var array */
        $fields = $request->query->get('fields', null);
        
        $this->get('logger')->info(sprintf("Get a User by id [id: %d | fields: [%s]]", $id, $fields),
            [ 'id' => $id, 'request' => $request]);
        
        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.user_manager');
        /** @var User */
        $user = (empty($fields)) ? $manager->read($id) : $manager->read($id, explode(",", $fields));
        $restData = new RestDataResponse($user, "/rest/users/$id");
        
        $this->get('logger')->info("One user found", [ "response" => $restData]);
        
        return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_OK,
            [ "Location" => $request->getUri()], true);
    }


    /**
     * Create a new User
     *
     * @Rest\Post("", name="rest_create_user")
     * @Rest\RequestParam(name="user", requirements="array", description="The user data to post", nullable=false)
     * @ApiDoc(
     *   section="Users",
     *   description="Create a new user",
     *   input={ "class"=UserType::class },
     *   statusCodes={
     *     201="Created",
     *     400="Invalid form",
     *     401="Unauthorized access",
     *     403="Forbidden access"
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
        $postData = $request->request->get('user', [ ]);
        
        $this->get('logger')->info(sprintf("Post a new User"), [ 'request' => $request]);
        
        try {
            /** @var User */
            $user = $this->get('coloc_matching.core.user_manager')->create($postData);
            $restData = new RestDataResponse($user, '/rest/users/' . $user->getId());
            
            $this->get('logger')->info(sprintf("User created [user: %s]", $user), [ 'response' => $restData]);
            
            return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_CREATED,
                [ "Location" => $request->getUri()], true);
        }
        catch (InvalidFormDataException $e) {
            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ "Location" => $request->getUri()],
                true);
        }
    }


    /**
     * Update an existing user
     *
     * @Rest\Put("/{id}", name="rest_update_user")
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
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     404="User not found"
     *
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
        $this->get('logger')->info(sprintf("Put a User with the following id [id: %d]", $id),
            [ 'id' => $id, 'request' => $request]);
        
        return $this->handleUpdateRequest($id, $request, true);
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
     *     401="Unauthorized access",
     *     403="Forbidden access",
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
        $this->get('logger')->info(sprintf("Patch a User with the following id [id: %d]", $id),
            [ 'id' => $id, 'request' => $request]);
        
        return $this->handleUpdateRequest($id, $request, false);
    }


    /**
     * Delete an existing user
     *
     * @Rest\Delete("/{id}", name="rest_delete_user")
     * @ApiDoc(
     *   section="Users",
     *   description="Delete an existing user",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The user id" }
     *   },
     *   statusCodes={
     *     200="OK",
     *     401="Unauthorized access",
     *     403="Forbidden access"
     * })
     *
     * @Security(expression="has_role('ROLE_ADMIN')")
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteUserAction(int $id) {
        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.user_manager');
        
        $this->get('logger')->info(sprintf("Delete a User with the following id [id: %d]", $id), [ 'id' => $id]);
        
        /** @var User */
        $user = $manager->read($id);
        
        if ($user) {
            $this->get('logger')->info(sprintf("User found [user: %s]", $user));
            
            $manager->delete($user);
        }
        
        return new JsonResponse("User deleted", Response::HTTP_OK, [ ], true);
    }


    /**
     * Get a user's announcement
     *
     * @Rest\Get("/{id}/announcement", name="rest_get_user_announcement")
     * @ApiDoc(
     *   section="Users",
     *   resource=true,
     *   description="Get a user's announcement",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The user id" }
     *   },
     *   output="ColocMatching\CoreBundle\Entity\Announcement\Announcement",
     *   statusCodes={
     *     200="OK",
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     404="User not found"
     * })
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getAnnouncementAction(int $id) {
        $this->get('logger')->info(sprintf("Get a User's announcement by user id [id: %d]", $id), [ 'id' => $id]);
        
        /** @var User */
        $user = $this->get('coloc_matching.core.user_manager')->read($id);
        $restData = new RestDataResponse($user->getAnnouncement(), "/rest/users/$id/announcement");
        
        $this->get('logger')->info(
            sprintf("User's announcement found [id: %d | announcement: %s]", $user->getId(), $user->getAnnouncement()),
            [ 'response' => $restData]);
        
        return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_OK, [ ], true);
    }


    /**
     * Get a user's picture by id
     *
     * @Rest\Get("/{id}/picture", name="rest_get_user_picture")
     * @ApiDoc(
     *   section="Users",
     *   resource=true,
     *   description="Get a user's picture by id",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The user id" }
     *   },
     *   output="ColocMatching\CoreBundle\Entity\User\ProfilePicture",
     *   statusCodes={
     *     201="Picture uploaded",
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     404="User not found"
     * })
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getPictureAction(int $id) {
        $this->get('logger')->info(sprintf("Get a User's picture by user id [id: %d]", $id), [ 'id' => $id]);
        
        /** @var User */
        $user = $this->get('coloc_matching.core.user_manager')->read($id);
        $restData = new RestDataResponse($user->getPicture(), "/rest/users/$id/picture");
        
        $this->get('logger')->info(
            sprintf("User's picture found [id: %d | picture: %s]", $user->getId(), $user->getPicture()),
            [ 'response' => $restData]);
        
        return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_OK, [ ], true);
    }


    /**
     * @Rest\Post("/{id}/picture", name="rest_post_user_picture")
     * @Rest\FileParam(name="file", image=true, nullable=false, description="The picture to upload")
     * @ApiDoc(
     *   section="Users",
     *   description="Upload a user's picture by id",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The user id" }
     *   },
     *   input={ "class"=FileType::class },
     *   statusCodes={
     *     200="OK",
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     404="User not found"
     * })
     *
     * @param int $id
     * @param Request $request
     */
    public function uploadPictureAction(int $id, Request $request) {
        $this->get("logger")->info(sprintf("Upload a profile picture for the user [id: %d]", $id),
            [ "id" => $id, "request" => $request]);
        
        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.user_manager');
        
        /** @var User */
        $user = $manager->read($id);
        /** @var File */
        $file = $request->files->get("file");
        
        try {
            $user = $manager->uploadProfilePicture($user, $file);
            $restData = new RestDataResponse($user->getPicture(), "/user/$id/picture");
            
            $this->get('logger')->info(sprintf("Profie picture uploaded [profilePicture: %s]", $user->getPicture()),
                [ 'response' => $restData]);
            
            return new JsonResponse($this->get("jms_serializer")->serialize($restData, "json"), Response::HTTP_OK,
                [ "Location" => $request->getUri()], true);
        }
        catch (InvalidFormDataException $e) {
            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ "Location" => $request->getUri()],
                true);
        }
    }


    /**
     * @Rest\Delete("/{id}/picture", name="rest_delete_user_picture")
     * @ApiDoc(
     *   section="Users",
     *   description="Delete a user's picture by id",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The user id" }
     *   },
     *   statusCodes={
     *     200="OK",
     *     401="Unauthorized access",
     *     403="Forbidden access"
     * })
     *
     * @param int $id
     */
    public function deletePictureAction(int $id) {
        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.user_manager');
        
        $this->get('logger')->info(sprintf("Delete a User's profile picture with the following id [id: %d]", $id),
            [ 'id' => $id]);
        
        /** @var User */
        $user = $manager->read($id);
        
        if (!empty($user->getPicture())) {
            $this->get('logger')->info(sprintf("User found [user: %s]", $user));
            
            $manager->deleteProfilePicture($user);
        }
        
        return new JsonResponse("User's profile picture deleted", Response::HTTP_OK, [ ], true);
    }


    private function handleUpdateRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.user_manager');
        /** @var User */
        $user = $manager->read($id);
        
        if (!$user) {
            $this->get('logger')->error(sprintf("No User found [id: %d]", $id), [ 'id' => $id,
                'request' => $request]);
            
            throw new NotFoundHttpException("User not found with the Id $id");
        }
        
        /** @var array */
        $data = $request->request->get('user', [ ]);
        
        try {
            if ($fullUpdate) {
                /** @var User */
                $user = $manager->update($user, $data);
            }
            else {
                /** @var User */
                $user = $manager->partialUpdate($user, $data);
            }
            
            $restData = new RestDataResponse($user, "/rest/users/$id");
            
            $this->get('logger')->info(sprintf("User updated [user: %s]", $user), [ 'response' => $restData]);
            
            return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_OK,
                [ "Location" => $request->getUri()], true);
        }
        catch (InvalidFormDataException $e) {
            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ "Location" => $request->getUri()],
                true);
        }
    }

}
