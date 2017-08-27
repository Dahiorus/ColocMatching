<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\Group;

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Definition(
 *   definition="GroupPictureResponse",
 *   allOf={
 *     {"$ref"="#/definitions/EntityResponse"}
 *   },
 *   @SWG\Property(property="content", ref="#/definitions/GroupPicture")
 * )
 *
 * @SWG\Tag(name="Groups - picture", description="Group's picture")
 *
 * @author Dahiorus
 */
interface GroupPictureControllerInterface {

    /**
     * Gets the picture of an existing group
     *
     * @SWG\Get(path="/groups/{id}/picture", operationId="rest_get_group_picture",
     *   tags={ "Groups - picture" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
     *   ),
     *
     *   @SWG\Response(
     *     response=200, description="Group found and picture returned",
     *     @SWG\Schema(ref="#/definitions/GroupPictureResponse")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No group found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getGroupPictureAction(int $id);


    /**
     * Uploads a file as the picture of an existing group
     *
     * @SWG\Post(path="/groups/{id}/picture", operationId="rest_upload_group_picture",
     *   tags={ "Groups - picture" },
     *   consumes={"multipart/form-data"},
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
     *   ),
     *   @SWG\Parameter(
     *     in="formData", name="file", type="file", required=true,
     *     description="The file to upload as the new group picture"
     *   ),
     *
     *   @SWG\Response(
     *     response=200, description="Group found and picture uploaded",
     *     @SWG\Schema(ref="#/definitions/GroupPictureResponse")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No group found")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function uploadGroupPictureAction(int $id, Request $request);


    /**
     * Deletes the picture of an existing group
     *
     * @SWG\Delete(path="/groups/{id}/picture", operationId="rest_delete_group_picture",
     *   tags={ "Groups - picture" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Group found and picture deleted"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No group found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteGroupPictureAction(int $id);
}