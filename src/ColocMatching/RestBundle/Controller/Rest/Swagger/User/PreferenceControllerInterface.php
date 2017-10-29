<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\User;

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Tag(name="Users - preferences", description="User's searching preferences")
 *
 * @author Dahiorus
 */
interface PreferenceControllerInterface {

    /**
     * Gets the user search preference of an existing user
     *
     * @SWG\Get(path="/users/{id}/preferences/user", operationId="rest_get_user_user_preference",
     *   tags={ "Users - preferences" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(
     *     response=200, description="User found and user preference returned",
     *     @SWG\Schema(ref="#/definitions/UserPreference")),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getUserPreferenceAction(int $id);


    /**
     * Updates the user search preference of an existing user
     *
     * @SWG\Put(path="/users/{id}/preferences/user", operationId="rest_update_user_user_preference",
     *   tags={ "Users - preferences" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(
     *     in="body", name="userPreference", required=true, description="The data to put",
     *     @SWG\Schema(ref="#/definitions/UserPreference")),
     *   @SWG\Response(response=200, description="User's user preference updated",
     *     @SWG\Schema(ref="#/definitions/UserPreference")),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found"),
     *   @SWG\Response(response="422", description="Validation error")
     * )
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateUserPreferenceAction(int $id, Request $request);


    /**
     * Updates (partial) the user search preference of an existing user
     *
     * @SWG\Patch(path="/users/{id}/preferences/user", operationId="rest_patch_user_user_preference",
     *   tags={ "Users - preferences" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(
     *     in="body", name="userPreference", required=true, description="The data to patch",
     *     @SWG\Schema(ref="#/definitions/UserPreference")),
     *   @SWG\Response(response=200, description="User's user preference updated",
     *     @SWG\Schema(ref="#/definitions/UserPreference")),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found"),
     *   @SWG\Response(response="422", description="Validation error")
     * )
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function patchUserPreferenceAction(int $id, Request $request);


    /**
     * Gets the announcement search preference of an existing user
     *
     * @SWG\Get(path="/users/{id}/preferences/announcement", operationId="rest_get_user_announcement_preference",
     *   tags={ "Users - preferences" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(
     *     response=200, description="User found and announcement preference returned",
     *     @SWG\Schema(ref="#/definitions/AnnouncementPreference")),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getAnnouncementPreferenceAction(int $id);


    /**
     * Updates the announcement search preference of an existing user
     *
     * @SWG\Put(path="/users/{id}/preferences/announcement", operationId="rest_update_user_announcement_preference",
     *   tags={ "Users - preferences" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(
     *     in="body", name="announcementPreference", required=true, description="The data to put",
     *     @SWG\Schema(ref="#/definitions/AnnouncementPreference")),
     *   @SWG\Response(response=200, description="User's announcement preference updated",
     *     @SWG\Schema(ref="#/definitions/AnnouncementPreference")),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found"),
     *   @SWG\Response(response="422", description="Validation error")
     * )
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAnnouncementPreferenceAction(int $id, Request $request);


    /**
     * Updates (partial) the announcement search preference of an existing user
     *
     * @SWG\Patch(path="/users/{id}/preferences/announcement", operationId="rest_patch_user_announcement_preference",
     *   tags={ "Users - preferences" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(
     *     in="body", name="announcementPreference", required=true,description="The data to patch",
     *     @SWG\Schema(ref="#/definitions/AnnouncementPreference")),
     *   @SWG\Response(response=200, description="User's user preference updated",
     *     @SWG\Schema(ref="#/definitions/AnnouncementPreference")),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found"),
     *   @SWG\Response(response="422", description="Validation error")
     * )
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function patchAnnouncementPreferenceAction(int $id, Request $request);
}