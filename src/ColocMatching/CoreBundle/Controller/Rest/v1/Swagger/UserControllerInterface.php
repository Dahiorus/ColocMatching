<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1\Swagger;

use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ColocMatching\CoreBundle\Entity\User\UserPreference;

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
 * @SWG\Tag(name="ProfilePicture", description="Operations on ProfilePicture")
 * @SWG\Tag(name="Profile", description="Operations on Profile")
 * @SWG\Tag(name="UserPreference", description="Operations on UserPreference")
 * @SWG\Tag(name="AnnouncementPreference", description="Operations on AnnouncementPreference")
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
     *     in="query", name="order", type="string", enum={"asc", "desc"}, default="asc",
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
     *   tags={ "Users"},
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
     *   tags={ "Users" },
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
     * Searches users by criteria
     *
     * @SWG\Post(path="/users/searches/", operationId="rest_search_users",
     *   tags={ "Users" },
     *
     *   @SWG\Parameter(
     *     in="body", name="filter", required=true,
     *     description="The user filter data",
     *
     *     @SWG\Schema(ref="#/definitions/UserFilter")
     *   ),
     *
     *   @SWG\Response(response=200, description="Users found",
     *     @SWG\Schema(ref="#/definitions/UserListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found",
     *     @SWG\Schema(ref="#/definitions/UserListResponse")
     *   ),
     *   @SWG\Response(response=400, description="Bad request")
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchUsersAction(Request $request);


    /**
     * Gets the announcement of an existing user
     *
     * @SWG\Get(path="/users/{id}/announcement", operationId="rest_get_user_announcement",
     *   tags={ "Users" },
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
     *   tags={ "ProfilePicture" },
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
     *   tags={ "ProfilePicture" },
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
     *   tags={ "ProfilePicture" },
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


    /**
     * Gets the profile of an existing user
     *
     * @SWG\Get(path="/users/{id}/profile", operationId="rest_get_user_profile",
     *   tags={ "Profile" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *
     *   @SWG\Response(
     *     response=200, description="User found and profile returned",
     *     @SWG\Schema(ref="#/definitions/Profile")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getProfileAction(int $id);


    /**
     * Updates the profile of an existing user
     *
     * @SWG\Put(path="/users/{id}/profile", operationId="rest_update_user_profile",
     *   tags={ "Profile" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="profile", required=true,
     *     description="The data to put",
     *
     *     @SWG\Schema(ref="#/definitions/Profile")
     *   ),
     *
     *   @SWG\Response(response=200, description="User's profile updated",
     *     @SWG\Schema(ref="#/definitions/Profile")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfileAction(int $id, Request $request);


    /**
     * Updates (partial) the profile of an existing user
     *
     * @SWG\Patch(path="/users/{id}/profile", operationId="rest_patch_user_profile",
     *   tags={ "Profile" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="profile", required=true,
     *     description="The data to patch",
     *
     *     @SWG\Schema(ref="#/definitions/Profile")
     *   ),
     *
     *   @SWG\Response(response=200, description="User's profile updated",
     *     @SWG\Schema(ref="#/definitions/Profile")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function patchProfileAction(int $id, Request $request);


    /**
     * Gets the user search preference of an existing user
     *
     * @SWG\Get(path="/users/{id}/preferences/user", operationId="rest_get_user_user_preference",
     *   tags={ "UserPreference" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *
     *   @SWG\Response(
     *     response=200, description="User found and user preference returned",
     *     @SWG\Schema(ref="#/definitions/UserPreference")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getUserPreferenceAction(int $id);


    /**
     * Updates the user search preference of an existing user
     *
     * @SWG\Put(path="/users/{id}/preferences/user", operationId="rest_update_user_user_preference",
     *   tags={ "UserPreference" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="userPreference", required=true,
     *     description="The data to put",
     *
     *     @SWG\Schema(ref="#/definitions/UserPreference")
     *   ),
     *
     *   @SWG\Response(response=200, description="User's user preference updated",
     *     @SWG\Schema(ref="#/definitions/UserPreference")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateUserPreferenceAction(int $id, Request $request);


    /**
     * Updates (partial) the user search preference of an existing user
     *
     * @SWG\Patch(path="/users/{id}/preferences/user", operationId="rest_patch_user_user_preference",
     *   tags={ "UserPreference" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="userPreference", required=true,
     *     description="The data to patch",
     *
     *     @SWG\Schema(ref="#/definitions/UserPreference")
     *   ),
     *
     *   @SWG\Response(response=200, description="User's user preference updated",
     *     @SWG\Schema(ref="#/definitions/UserPreference")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function patchUserPreferenceAction(int $id, Request $request);


    /**
     * Gets the announcement search preference of an existing user
     *
     * @SWG\Get(path="/users/{id}/preferences/announcement", operationId="rest_get_user_announcement_preference",
     *   tags={ "AnnouncementPreference" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *
     *   @SWG\Response(
     *     response=200, description="User found and announcement preference returned",
     *     @SWG\Schema(ref="#/definitions/AnnouncementPreference")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getAnnouncementPreferenceAction(int $id);


    /**
     * Updates the announcement search preference of an existing user
     *
     * @SWG\Put(path="/users/{id}/preferences/announcement", operationId="rest_update_user_announcement_preference",
     *   tags={ "AnnouncementPreference" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="announcementPreference", required=true,
     *     description="The data to put",
     *
     *     @SWG\Schema(ref="#/definitions/AnnouncementPreference")
     *   ),
     *
     *   @SWG\Response(response=200, description="User's announcement preference updated",
     *     @SWG\Schema(ref="#/definitions/AnnouncementPreference")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAnnouncementPreferenceAction(int $id, Request $request);


    /**
     * Updates (partial) the announcement search preference of an existing user
     *
     * @SWG\Patch(path="/users/{id}/preferences/announcement", operationId="rest_patch_user_announcement_preference",
     *   tags={ "AnnouncementPreference" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="announcementPreference", required=true,
     *     description="The data to patch",
     *
     *     @SWG\Schema(ref="#/definitions/AnnouncementPreference")
     *   ),
     *
     *   @SWG\Response(response=200, description="User's user preference updated",
     *     @SWG\Schema(ref="#/definitions/AnnouncementPreference")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function patchAnnouncementPreferenceAction(int $id, Request $request);

}