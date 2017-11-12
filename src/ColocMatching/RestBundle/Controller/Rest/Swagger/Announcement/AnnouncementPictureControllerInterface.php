<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\Announcement;

use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Tag(name="Announcements - pictures", description="Pictures of announcement")
 *
 * @author Dahiorus
 */
interface AnnouncementPictureControllerInterface {

    /**
     * Uploads a new picture for an existing announcement
     *
     * @SWG\Post(path="/announcements/{id}/pictures", operationId="rest_upload_announcement_picture",
     *   tags={ "Announcements - pictures" }, security={
     *     { "api_token" = {} }
     *   }, consumes={ "multipart/form-data" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(
     *     in="formData", name="file", type="file", required=true, description="The file to upload as the new picture"),
     *   @SWG\Response(
     *     response=201, description="Announcement found and picture uploaded",
     *     @SWG\Schema(title="pictures", type="array", @SWG\Items(ref="#/definitions/AnnouncementPicture"))),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function uploadAnnouncementPictureAction(int $id, Request $request);


    /**
     * Deletes a picture from an existing announcement
     *
     * @SWG\Delete(path="/announcements/{id}/pictures/{pictureId}", operationId="rest_delete_announcement_picture",
     *   tags={ "Announcements - pictures" },  security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(
     *     in="path", name="pictureId", type="integer", required=true, description="The picture identifier"),
     *   @SWG\Response(response=200, description="Announcement found and picture deleted"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found")
     * )
     *
     * @param int $id
     * @param int $pictureId
     *
     * @throws AnnouncementNotFoundException
     */
    public function deleteAnnouncementPictureAction(int $id, int $pictureId);
}