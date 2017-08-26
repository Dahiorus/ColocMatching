<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Swagger\User;

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Definition(
 *   definition="ProfilePictureResponse",
 *   allOf={
 *     {"$ref"="#/definitions/EntityResponse"}
 *   },
 *   @SWG\Property(property="content", ref="#/definitions/ProfilePicture")
 * )
 *
 * @SWG\Tag(name="Users - profile picture", description="User's profile picture")
 *
 * @author Dahiorus
 */
interface ProfilePictureControllerInterface {

    /**
     * Gets the profile picture of an existing user
     *
     * @SWG\Get(path="/users/{id}/picture", operationId="rest_get_user_picture",
     *   tags={ "Users - profile picture" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *
     *   @SWG\Response(response=200, description="User found and picture returned",
     *     @SWG\Schema(ref="#/definitions/ProfilePictureResponse")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getPictureAction(int $id);


    /**
     * Uploads a file as the profile picture of an existing user
     *
     * @SWG\Post(path="/users/{id}/picture", operationId="rest_upload_user_picture",
     *   tags={ "Users - profile picture" },
     *   consumes={ "multipart/form-data" },
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
     *   @SWG\Response(response=200, description="User found and picture uploaded",
     *     @SWG\Schema(ref="#/definitions/ProfilePictureResponse")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function uploadPictureAction(int $id, Request $request);


    /**
     * Deletes the profile picture of an existing user
     *
     * @SWG\Delete(path="/users/{id}/picture", operationId="rest_delete_user_picture",
     *   tags={ "Users - profile picture" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The User id"
     *   ),
     *
     *   @SWG\Response(response=200, description="User found and profile picture deleted"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deletePictureAction(int $id);
}