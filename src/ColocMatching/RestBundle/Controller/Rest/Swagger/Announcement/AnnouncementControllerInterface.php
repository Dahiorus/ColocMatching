<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\Announcement;

use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use FOS\RestBundle\Request\ParamFetcher;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @SWG\Definition(
 *   definition="AnnouncementListResponse",
 *   allOf={
 *     {"$ref"="#/definitions/PageResponse"}
 *   },
 *   @SWG\Property(property="content", type="array",
 *     @SWG\Items(ref="#/definitions/Announcement")
 * ))
 *
 * @SWG\Definition(
 *   definition="AnnouncementResponse",
 *   allOf={
 *     {"$ref"="#/definitions/EntityResponse"}
 *   },
 *   @SWG\Property(property="content", ref="#/definitions/Announcement")
 * )
 *
 * @SWG\Tag(name="Announcements")
 *
 * @author Dahiorus
 */
interface AnnouncementControllerInterface {

    /**
     * Lists announcements or specified fields with pagination
     *
     * @SWG\Get(path="/announcements", operationId="rest_get_announcements",
     *   tags={ "Announcements" },
     *
     *   @SWG\Parameter(
     *     in="query", name="page", type="integer", default=1, minimum=0,
     *     description="The page of the paginated search"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="size", type="integer", default=20, minimum=1,
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
     *   @SWG\Response(response=206, description="Partial content found")
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getAnnouncementsAction(ParamFetcher $paramFetcher);


    /**
     * Creates a new announcement for the authenticated user
     *
     * @SWG\Post(path="/announcements", operationId="rest_create_announcement",
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
     *     @SWG\Schema(ref="#/definitions/AnnouncementResponse")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=422, description="Cannot recreate an announcement")
     * )
     *
     * @param Request $request
     *
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
     *     @SWG\Schema(ref="#/definitions/AnnouncementResponse")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found")
     * )
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
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
     *     @SWG\Schema(ref="#/definitions/AnnouncementResponse")
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
     *     in="body", name="announcement", required=true,
     *     description="The data to patch",
     *
     *     @SWG\Schema(ref="#/definitions/Announcement")
     *   ),
     *
     *   @SWG\Response(response=200, description="Announcement updated",
     *     @SWG\Schema(ref="#/definitions/AnnouncementResponse")
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
     *
     * @return JsonResponse
     */
    public function deleteAnnouncementAction(int $id);


    /**
     * Searches announcements by criteria
     *
     * @SWG\Post(path="/announcements/searches", operationId="rest_search_announcements",
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
     *   @SWG\Response(response=206, description="Partial content found"),
     *   @SWG\Response(response=400, description="Bad request")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchAnnouncementsAction(Request $request);


    /**
     * Gets the location of an existing announcement
     *
     * @SWG\Get(path="/announcements/{id}/location", operationId="rest_get_announcement_location",
     *   tags={ "Announcements" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Announcement found and location returned",
     *     @SWG\Schema(ref="#/definitions/Address")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found")
     * )
     *
     * @param int $id
     */
    public function getAnnouncementLocationAction(int $id);


    /**
     * Gets all candidates of an existing announcement
     *
     * @SWG\Get(path="/announcements/{id}/candidates", operationId="rest_get_announcement_candidates",
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
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function getCandidatesAction(int $id);


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
     *   @SWG\Response(response=200, description="Announcement found and candidate removed"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found")
     * )
     *
     * @param int $id
     * @param int $userId
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function removeCandidateAction(int $id, int $userId);

}