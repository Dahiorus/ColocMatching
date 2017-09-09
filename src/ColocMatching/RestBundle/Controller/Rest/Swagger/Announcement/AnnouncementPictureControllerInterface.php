<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\Announcement;

use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\AnnouncementPictureNotFoundException;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Definition(
 *   definition="AnnouncementPictureListResponse",
 *   allOf={
 *     {"$ref"="#/definitions/EntityResponse"}
 *   },
 *   @SWG\Property(property="content", type="array",
 *     @SWG\Items(ref="#/definitions/AnnouncementPicture")
 * ))
 *
 * @SWG\Definition(
 *   definition="AnnouncementPictureResponse",
 *   allOf={
 *     {"$ref"="#/definitions/EntityResponse"}
 *   },
 *   @SWG\Property(property="content", ref="#/definitions/AnnouncementPicture")
 * )
 *
 * @SWG\Tag(name="Announcements - pictures", description="Pictures of announcement")
 *
 * @author Dahiorus
 */
interface AnnouncementPictureControllerInterface {

    /**
     * Gets all pictures of an existing announcement
     *
     * @SWG\Get(path="/announcements/{id}/pictures", operationId="rest_get_announcement_pictures",
     *   tags={ "Announcements - pictures" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Announcement found and pictures returned",
     *     @SWG\Schema(ref="#/definitions/AnnouncementPictureListResponse")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found")
     * )
     *
     * @param int $id
     *
     * @throws AnnouncementNotFoundException
     * @return JsonResponse
     */
    public function getAnnouncementPicturesAction(int $id);


    /**
     * Uploads a new picture for an existing announcement
     *
     * @SWG\Post(path="/announcements/{id}/pictures", operationId="rest_upload_announcement_picture",
     *   tags={ "Announcements - pictures" },
     *   consumes={ "multipart/form-data" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *   @SWG\Parameter(
     *     in="formData", name="file", type="file", required=true,
     *     description="The file to upload as the new picture"
     *   ),
     *
     *   @SWG\Response(response=201, description="Announcement found and picture uploaded",
     *     @SWG\Schema(ref="#/definitions/AnnouncementPictureResponse")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found")
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
     * Gets a picture of an existing announcement
     *
     * @SWG\Get(path="/announcements/{id}/pictures/{pictureId}", operationId="rest_get_announcement_picture",
     *   tags={ "Announcements - pictures" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *   @SWG\Parameter(
     *     in="path", name="pictureId", type="integer", required=true,
     *     description="The AnnouncementPicture id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Announcement found and picture returned",
     *     @SWG\Schema(ref="#/definitions/AnnouncementPictureResponse")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found or no AnnouncementPicture found")
     * )
     *
     * @param int $id
     * @param int $pictureId
     *
     * @throws AnnouncementNotFoundException
     * @throws AnnouncementPictureNotFoundException
     * @return JsonResponse
     */
    public function getAnnouncementPictureAction(int $id, int $pictureId);


    /**
     * Deletes a picture from an existing announcement
     *
     * @SWG\Delete(path="/announcements/{id}/pictures/{pictureId}", operationId="rest_delete_announcement_picture",
     *   tags={ "Announcements - pictures" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *   @SWG\Parameter(
     *     in="path", name="pictureId", type="integer", required=true,
     *     description="The AnnouncementPicture id"
     *   ),
     *
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