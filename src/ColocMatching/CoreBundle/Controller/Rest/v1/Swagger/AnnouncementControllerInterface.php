<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1\Swagger;

use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Definition(
 *   definition="AnnouncementListResponse",
 *   allOf={
 *     {"$ref"="#/definitions/RestListResponse"}
 *   },
 *
 *   @SWG\Property(property="data", type="array",
 *     @SWG\Items(ref="#/definitions/Announcement")
 * ))
 *
 * @SWG\Tag(name="Announcements", description="Operations on Announcement")
 * @SWG\Tag(name="AnnouncementPictures", description="Operations on AnnouncementPicture")
 *
 * @author Dahiorus
 */
interface AnnouncementControllerInterface {


    /**
     * Lists announcements or specified fields with pagination
     *
     * @SWG\Get(path="/announcements/", operationId="rest_get_annoucements",
     *   tags={ "Announcements" },
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
     *   @SWG\Response(response=200, description="Announcements found",
     *     @SWG\Schema(ref="#/definitions/AnnouncementListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found",
     *     @SWG\Schema(ref="#/definitions/AnnouncementListResponse")
     * ))
     *
     * @param Request $paramFetcher
     * @return JsonResponse
     */
    public function getAnnouncementsAction(ParamFetcher $paramFetcher);


    /**
     * Creates a new announcement for the authenticated user
     *
     * @SWG\Post(path="/announcements/", operationId="rest_create_announcement",
     *   tags={ "Announcements" },
     *
     *   @SWG\Parameter(
     *     in="body", name="announcement", required=true,
     *     description="The data to post",
     *
     *     @SWG\Schema(ref="#/definitions/Announcement")
     *   ),
     *
     *   @SWG\Response(response=201, description="Announcement created",
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=422, description="Cannot recreate an announcement")
     * )
     *
     * @param Request $request
     * @return JsonResponse
     * @throws JWTDecodeFailureException
     * @throws UnprocessableEntityHttpException
     */
    public function createAnnouncementAction(Request $request);


    /**
     * Gets an existing announcement or its fields
     *
     * @SWG\Get(path="/announcements/{id}", operationId="rest_get_announcement",
     *   tags={ "Announcements" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="fields", type="array",
     *     description="The fields to return",
     *     uniqueItems=true, collectionFormat="csv",
     *
     *     @SWG\Items(type="string")
     *   ),
     *
     *   @SWG\Response(response=200, description="Announcement found",
     *     @SWG\Schema(ref="#/definitions/Announcement")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found")
     * )
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function getAnnouncementAction(int $id, ParamFetcher $paramFetcher);


    /**
     * Updates an existing announcement
     *
     * @SWG\Put(path="/announcements/{id}", operationId="rest_update_announcement",
     *   tags={ "Announcements" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="user", required=true,
     *     description="The data to put",
     *
     *     @SWG\Schema(ref="#/definitions/Announcement")
     *   ),
     *
     *   @SWG\Response(response=200, description="Announcement updated",
     *     @SWG\Schema(ref="#/definitions/Announcement")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=403, description="No Announcement found")
     * )
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function updateAnnouncementAction(int $id, Request $request);


    /**
     * Updates (partial) an existing announcement
     *
     * @SWG\Patch(path="/announcements/{id}", operationId="rest_patch_announcement",
     *   tags={ "Announcements" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="user", required=true,
     *     description="The data to patch",
     *
     *     @SWG\Schema(ref="#/definitions/Announcement")
     *   ),
     *
     *   @SWG\Response(response=200, description="Announcement updated",
     *     @SWG\Schema(ref="#/definitions/Announcement")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found")
     * )
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function patchAnnouncementAction(int $id, Request $request);


    /**
     * Deletes an existing announcement
     *
     * @SWG\Delete(path="/announcements/{id}", operationId="rest_delete_announcement",
     *   tags={ "Announcements" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Announcement deleted"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access")
     * )
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAnnouncementAction(int $id);


    /**
     * Searches announcements by criteria
     *
     * @SWG\Post(path="/announcements/searches/", operationId="rest_search_announcements",
     *   tags={ "Announcements" },
     *
     *   @SWG\Parameter(
     *     in="body", name="filter", required=true,
     *     description="The announcement filter data",
     *
     *     @SWG\Schema(ref="#/definitions/AnnouncementFilter")
     *   ),
     *
     *   @SWG\Response(response=200, description="Announcements found",
     *     @SWG\Schema(ref="#/definitions/AnnouncementListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found",
     *     @SWG\Schema(ref="#/definitions/AnnouncementListResponse")
     *   ),
     *   @SWG\Response(response=400, description="Bad request")
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchAnnouncementsAction(Request $request);


    /**
     * Gets all pictures of an existing announcement
     *
     * @SWG\Get(path="/announcements/{id}/pictures/", operationId="rest_get_announcement_pictures",
     *   tags={ "AnnouncementPictures" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Announcement found and pictures returned",
     *     @SWG\Schema(title="Pictures", type="array",
     *       @SWG\Items(title="AnnouncementPicture", ref="#/definitions/AnnouncementPicture")
     *   )),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found")
     * )
     *
     * @param int $id
     * @throws NotFoundHttpException
     * @return JsonResponse
     */
    public function getAnnouncementPicturesAction(int $id);


    /**
     * Uploads a new picture for an existing announcement
     *
     * @SWG\Post(path="/announcements/{id}/pictures/", operationId="rest_upload_announcement_picture",
     *   tags={ "AnnouncementPictures" },
     *   consumes={"multipart/form-data"},
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
     *     @SWG\Schema(title="Pictures", type="array",
     *       @SWG\Items(title="AnnouncementPicture", ref="#/definitions/AnnouncementPicture")
     *   )),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found")
     * )
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function uploadNewAnnouncementPicture(int $id, Request $request);


    /**
     * Gets a picture of an existing announcement
     *
     * @SWG\Get(path="/announcements/{id}/pictures/{pictureId}", operationId="rest_get_announcement_picture",
     *   tags={ "AnnouncementPictures" },
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
     *     @SWG\Schema(ref="#/definitions/AnnouncementPicture")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found or no AnnouncementPicture found")
     * )
     *
     * @param int $id
     * @param int $pictureId
     * @throws NotFoundHttpException
     * @return JsonResponse
     */
    public function getAnnouncementPictureAction(int $id, int $pictureId);


    /**
     * Deletes a picture from an existing announcement
     *
     * @SWG\Delete(path="/announcements/{id}/pictures/{pictureId}", operationId="rest_delete_announcement_picture",
     *   tags={ "AnnouncementPictures" },
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
     * @param int $announcementId
     * @param int $pictureId
     */
    public function deleteAnnouncementPictureAction(int $id, int $pictureId);


    /**
     * Gets all candidates of an existing announcement
     *
     * @SWG\Get(path="/announcements/{id}/candidates/", operationId="rest_get_announcement_candidates",
     *   tags={ "Announcements" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Announcement found and candidates returned",
     *     @SWG\Schema(title="Candidates", type="array",
     *       @SWG\Items(title="User", ref="#/definitions/User")
     *   )),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found")
     * )
     *
     * @param int $id
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function getCandidatesAction(int $id);


    /**
     * Adds the authenticated user as a candidate to an existing announcement
     *
     * @SWG\Post(path="/announcements/{id}/candidates/", operationId="rest_add_announcement_candidate",
     *   tags={"Announcements"},
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Announcement found and candidate added",
     *     @SWG\Schema(title="Candidates", type="array",
     *       @SWG\Items(title="User", ref="#/definitions/User")
     *   )),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found"),
     *   @SWG\Response(response=422, description="Cannot make creator a candidate of the announcement")
     * )
     *
     * @param int $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addNewCandidateAction(int $id, Request $request);


    /**
     * Removes a candidate from an existing announcement
     *
     * @SWG\Delete(path="/announcements/{id}/candidates/{userId}", operationId="rest_remove_announcement_candidate",
     *   tags={ "Announcements" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *   @SWG\Parameter(
     *     in="path", name="userId", type="integer", required=true,
     *     description="The candidate id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Announcement found and candidate removed",
     *     @SWG\Schema(title="Candidates", type="array",
     *       @SWG\Items(title="User", ref="#/definitions/User")
     *   )),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found")
     * )
     *
     * @param int $id
     * @param int $userId
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function removeCandidateAction(int $id, int $userId);

}