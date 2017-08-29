<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\Visit;

use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\VisitNotFoundException;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Definition(
 *   definition="AnnouncementVisitListResponse",
 *   allOf={
 *     {"$ref"="#/definitions/PageResponse"}
 *   },
 *   @SWG\Property(property="data", type="array",
 *     @SWG\Items(ref="#/definitions/AnnouncementVisit")
 * ))
 *
 * @SWG\Definition(
 *   definition="AnnouncementVisitResponse",
 *   allOf={
 *     {"$ref"="#/definitions/EntityResponse"}
 *   },
 *   @SWG\Property(property="content", ref="#/definitions/AnnouncementVisit")
 * )
 *
 * @SWG\Tag(name="Visits - announcements", description="Visits on announcements")
 */
interface AnnouncementVisitControllerInterface {

    /**
     * Lists the visits on one announcement with pagination
     *
     * @SWG\Get(path="/announcements/{id}/visits", operationId="rest_get_announcement_visits",
     *   tags={ "Visits - announcements" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The Announcement id"
     *   ),
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
     *
     *   @SWG\Response(response=200, description="Visits found",
     *     @SWG\Schema(ref="#/definitions/AnnouncementVisitListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found")
     * )
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function getVisitsAction(int $id, ParamFetcher $paramFetcher);


    /**
     * Gets an existing visit on an announcement
     *
     * @SWG\Get(path="/announcements/{id}visits/{visitId}", operationId="rest_get_announcement_visit",
     *   tags={ "Visits - announcements" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The announcement id"
     *   ),
     *   @SWG\Parameter(
     *     in="path", name="visitId", type="integer", required=true,
     *     description="The visit id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Visit found",
     *     @SWG\Schema(ref="#/definitions/AnnouncementVisitResponse")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No visit or announcement found")
     * )
     *
     * @param int $id
     * @param int $visitId
     *
     * @return JsonResponse
     * @throws VisitNotFoundException
     * @throws AnnouncementNotFoundException
     */
    public function getVisitAction(int $id, int $visitId);


    /**
     * Searches visits on an announcement by criteria
     *
     * @SWG\Post(path="/announcements/{id}/visits/searches", operationId="rest_search_announcement_visits",
     *   tags={ "Visits - announcements" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The announcement id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="filter", required=true,
     *     description="The visit filter data",
     *
     *     @SWG\Schema(ref="#/definitions/VisitFilter")
     *   ),
     *
     *   @SWG\Response(response=200, description="Announcements found",
     *     @SWG\Schema(ref="#/definitions/AnnouncementVisitListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found"),
     *   @SWG\Response(response=400, description="Bad request")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchVisitsAction(int $id, Request $request);

}