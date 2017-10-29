<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\User;

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Tag(name="Users - profile", description="User's profile")
 *
 * @author Dahiorus
 */
interface ProfileControllerInterface {

    /**
     * Gets the profile of an existing user
     *
     * @SWG\Get(path="/users/{id}/profile", operationId="rest_get_user_profile", tags={ "Users - profile" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(
     *     response=200, description="User found and profile returned", @SWG\Schema(ref="#/definitions/Profile")),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getProfileAction(int $id);


    /**
     * Updates the profile of an existing user
     *
     * @SWG\Put(path="/users/{id}/profile", operationId="rest_update_user_profile", tags={ "Users - profile" },
     *   security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(
     *     in="body", name="profile", required=true, description="The data to put",
     *     @SWG\Schema(ref="#/definitions/Profile")),
     *
     *   @SWG\Response(response=200, description="User's profile updated",@SWG\Schema(ref="#/definitions/Profile")),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No user found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateProfileAction(int $id, Request $request);


    /**
     * Updates (partial) the profile of an existing user
     *
     * @SWG\Patch(path="/users/{id}/profile", operationId="rest_patch_user_profile", tags={ "Users - profile" },
     *   security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(
     *     in="body", name="profile", required=true, description="The data to patch",
     *     @SWG\Schema(ref="#/definitions/Profile")),
     *   @SWG\Response(response=200, description="User's profile updated",
     *     @SWG\Schema(ref="#/definitions/Profile")),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function patchProfileAction(int $id, Request $request);
}