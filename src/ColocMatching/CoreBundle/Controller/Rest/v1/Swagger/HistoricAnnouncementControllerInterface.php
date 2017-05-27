<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1\Swagger;

use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Definition(
 *   definition="HistoricAnnouncementListResponse",
 *   allOf={
 *     {"$ref"="#/definitions/RestListResponse"}
 *   },
 *
 *   @SWG\Property(property="data", type="array",
 *     @SWG\Items(ref="#/definitions/HistoricAnnouncement")
 * ))
 *
 * @SWG\Definition(
 *   definition="HistoricAnnouncementDataResponse",
 *   allOf={
 *     {"$ref"="#/definitions/RestDataResponse"}
 *   },
 *
 *   @SWG\Property(property="data", type="array",
 *     @SWG\Items(ref="#/definitions/HistoricAnnouncement")
 * ))
 *
 * @SWG\Tag(name="HistoricAnnouncements", description="Operations about historic announcements")
 *
 * @author Dahiorus
 */
interface HistoricAnnouncementControllerInterface {


    /**
     * Lists historic announcements or specified fields with pagination
     *
     * @SWG\Get(path="/history/announcements", operationId="rest_get_historic_annoucements",
     *   tags={ "HistoricAnnouncements" },
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
     *   @SWG\Response(response=200, description="Historic announcements found",
     *     @SWG\Schema(ref="#/definitions/HistoricAnnouncementListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found",
     *     @SWG\Schema(ref="#/definitions/HistoricAnnouncementListResponse")
     * ))
     *
     * @param Request $paramFetcher
     * @return JsonResponse
     */
    public function getHistoricAnnouncementsAction(ParamFetcher $paramFetcher);


    /**
     * Gets an existing historic announcement or its fields
     *
     * @SWG\Get(path="/history/announcements/{id}", operationId="rest_get_historic_announcement",
     *   tags={ "HistoricAnnouncements" },
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
     *   @SWG\Response(response=200, description="Historic announcement found",
     *     @SWG\Schema(ref="#/definitions/HistoricAnnouncementDataResponse")
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
     * Searches historic announcements by criteria
     *
     * @SWG\Post(path="/history/announcements/searches", operationId="rest_search_historic_announcements",
     *   tags={ "HistoricAnnouncements" },
     *
     *   @SWG\Parameter(
     *     in="body", name="filter", required=true,
     *     description="The historic announcement filter data",
     *
     *     @SWG\Schema(ref="#/definitions/HistoricAnnouncementFilter")
     *   ),
     *
     *   @SWG\Response(response=200, description="Historic announcements found",
     *     @SWG\Schema(ref="#/definitions/HistoricAnnouncementListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found",
     *     @SWG\Schema(ref="#/definitions/HistoricAnnouncementListResponse")
     *   ),
     *   @SWG\Response(response=400, description="Bad request")
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchHistoricAnnouncementsAction(Request $request);

}