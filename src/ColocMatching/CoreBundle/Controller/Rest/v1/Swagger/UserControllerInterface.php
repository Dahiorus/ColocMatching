<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1\Swagger;

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Request\ParamFetcher;

/**
 * @SWG\Definition(
 *   definition="UserListResponse",
 *   allOf={
 *     {"$ref"="#/definitions/RestListResponse"}
 *   },
 *
 *   @SWG\Property(property="data", type="array",
 *     @SWG\Items(ref="#/definitions/User")
 * ))
 *
 * @SWG\Tag(name="Users", description="Operations on User")
 *
 * @author Dahiorus
 */
interface UserControllerInterface {


    /**
     * Lists users or fields with pagination
     *
     * @SWG\Get(path="/users/", operationId="rest_get_users",
     *   tags={ "Users" },
     *
     *   @SWG\Parameter(
     *     in="query", name="page", type="integer", default=1, minimum=0,
     *     description="The page of the paginated search"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="limit", type="integer", default=20, minimum=1,
     *     description="The number of results to return"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="sort", type="string", default="id",
     *     description="The name of the attribute to order the results"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="order", type="string", pattern="^(asc|desc)$", default="asc",
     *     description="The sort direction ('asc' for ascending sort, 'desc' for descending sort)"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="fields", type="array",
     *     description="The fields to return for each result",
     *     uniqueItems=true, collectionFormat="csv",
     *
     *     @SWG\Items(type="string")
     *   ),
     *
     *   @SWG\Response(response=200, description="Users found",
     *     @SWG\Schema(ref="#/definitions/UserListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found",
     *     @SWG\Schema(ref="#/definitions/UserListResponse")
     * ))
     *
     * @param ParamFetcher $paramFetcher
     * @return JsonResponse
     */
    public function getUsersAction(ParamFetcher $paramFetcher);


    /**
     * Creates a new user
     *
     * @SWG\Post(path="/users/", operationId="rest_create_user",
     *   tags={"Users"},
     *
     *   @SWG\Parameter(
     *     in="body", name="user", required=true,
     *     description="The data to post",
     *
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *
     *   @SWG\Response(response=201, description="User created",
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *   @SWG\Response(response=400, description="Bad request")
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createUserAction(Request $request);


    /**
     * Gets an existing user or its fields
     *
     * @SWG\Get(path="/users/{id}", operationId="rest_get_user",
     *   tags={ "Users" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="fields", type="array",
     *     description="The fields to return",
     *     uniqueItems=true, collectionFormat="csv",
     *
     *     @SWG\Items(type="string")
     *   ),
     *
     *   @SWG\Response(response=200, description="User found",
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getUserAction(int $id, ParamFetcher $paramFetcher);


    /**
     * Updates an existing user
     *
     * @SWG\Put(path="/users/{id}", operationId="rest_update_user",
     *   tags={ "Users" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="user", required=true,
     *     description="The data to put",
     *
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *
     *   @SWG\Response(response=200, description="User updated",
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateUserAction(int $id, Request $request);


    /**
     * Updates (partial) an existing user
     *
     * @SWG\Patch(path="/users/{id}", operationId="rest_patch_user",
     *   tags={ "Users" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="user", required=true,
     *     description="The data to patch",
     *
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *
     *   @SWG\Response(response=200, description="User updated",
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access")
     * )
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function patchUserAction(int $id, Request $request);


    /**
     * Deletes an existing user
     *
     * @SWG\Delete(path="/users/{id}", operationId="rest_delete_user",
     *   tags={ "Users", "Secured" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *
     *   @SWG\Response(response=200, description="User deleted"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access")
     * )
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteUserAction(int $id);


    /**
     * Gets the announcement of an existing user
     *
     * @SWG\Get(path="/users/{id}/announcement", operationId="rest_get_user_announcement",
     *   tags={"Users", "Announcements"},
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *
     *   @SWG\Response(response=200, description="User found and announcement returned",
     *     @SWG\Schema(ref="#/definitions/Announcement")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getAnnouncementAction(int $id);


    /**
     * Gets the profile picture of an existing user
     *
     * @SWG\Get(path="/users/{id}/picture", operationId="rest_get_user_picture",
     *   tags={"Users", "ProfilePictures"},
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *
     *   @SWG\Response(
     *     response=200, description="User found and picture returned",
     *     @SWG\Schema(ref="#/definitions/ProfilePicture")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getPictureAction(int $id);


    /**
     * Uploads a file as the profile picture of an existing user
     *
     * @SWG\Post(path="/users/{id}/picture", operationId="rest_upload_user_picture",
     *   tags={"Users", "ProfilePictures"},
     *   consumes={"multipart/form-data"},
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *   @SWG\Parameter(
     *     in="formData", name="file", type="file", required=true,
     *     description="The file to upload as the new profile picture"
     *   ),
     *
     *   @SWG\Response(response=200, description="User found and picture uploaded"),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadPictureAction(int $id, Request $request);


    /**
     * Deletes the profile picture of an existing user
     *
     * @SWG\Delete(path="/users/{id}/picture", operationId="rest_delete_user_picture",
     *   tags={ "Users", "ProfilePictures" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *
     *   @SWG\Response(response=200, description="User found and profile picture deleted"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access")
     * )
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deletePictureAction(int $id);

}