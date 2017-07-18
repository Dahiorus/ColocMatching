<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\Announcement;

use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Definition(
 *   definition="HousingResponse",
 *   allOf={
 *     { "$ref"="#/definitions/EntityResponse" }
 *   },
 *   @SWG\Property(property="content", ref="#/definitions/Housing")
 * )
 *
 * @SWG\Tag(name="Announcements - housing", description="The housing of an announcement")
 *
 * @author Dahiorus
 */
interface HousingControllerInterface {

    /**
     * Gets the housing of an existing announcement
     *
     * @SWG\Get(path="/announcements/{id}/housing", operationId="rest_get_announcement_housing",
     *   tags={ "Announcements - housing" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *
     *   @SWG\Response(
     *     response=200, description="Announcement found and housing returned",
     *     @SWG\Schema(ref="#/definitions/HousingResponse")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function getHousingAction(int $id);


    /**
     * Updates the housing of an existing announcement
     *
     * @SWG\Put(path="/announcements/{id}/housing", operationId="rest_update_announcement_housing",
     *   tags={ "Announcements - housing" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="housing", required=true,
     *     description="The data to put",
     *
     *     @SWG\Schema(ref="#/definitions/Housing")
     *   ),
     *
     *   @SWG\Response(
     *     response=200, description="Announcement's housing updated",
     *     @SWG\Schema(ref="#/definitions/HousingResponse")
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
    public function updateHousingAction(int $id, Request $request);


    /**
     * Updates (partial) the housing of an existing announcement
     *
     * @SWG\Patch(path="/announcements/{id}/housing", operationId="rest_patch_announcement_housing",
     *   tags={ "Announcements - housing" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="housing", required=true,
     *     description="The data to patch",
     *
     *     @SWG\Schema(ref="#/definitions/Housing")
     *   ),
     *
     *   @SWG\Response(
     *     response=200, description="Announcement's housing updated",
     *     @SWG\Schema(ref="#/definitions/HousingResponse")
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
    public function patchHousingAction(int $id, Request $request);
}