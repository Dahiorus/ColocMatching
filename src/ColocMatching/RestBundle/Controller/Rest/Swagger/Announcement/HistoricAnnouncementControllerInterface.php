<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\Announcement;

use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @SWG\Definition(
 *   definition="HistoricAnnouncementListResponse",
 *   allOf={
 *     {"$ref"="#/definitions/PageResponse"}
 *   },
 *   @SWG\Property(property="content", type="array",
 *     @SWG\Items(ref="#/definitions/HistoricAnnouncement")
 * ))
 *
 * @SWG\Definition(
 *   definition="HistoricAnnouncementResponse",
 *   allOf={
 *     {"$ref"="#/definitions/EntityResponse"}
 *   },
 *   @SWG\Property(property="content", ref="#/definitions/HistoricAnnouncement")
 * )
 *
 * @SWG\Tag(name="History - announcements", description="Historic announcements")
 *
 * @author Dahiorus
 */
interface HistoricAnnouncementControllerInterface {

    /**
     * Lists historic announcements or specified fields with pagination
     *
     * @SWG\Get(path="/history/announcements", operationId="rest_get_historic_announcements",
     *   tags={ "History - announcements" },
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
     *   @SWG\Response(response=200, description="HistoricAnnouncement announcements found",
     *     @SWG\Schema(ref="#/definitions/HistoricAnnouncementListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found")
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getHistoricAnnouncementsAction(ParamFetcher $paramFetcher);


    /**
     * Gets an existing historic announcement or its fields
     *
     * @SWG\Get(path="/history/announcements/{id}", operationId="rest_get_historic_announcement",
     *   tags={ "History - announcements" },
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
     *   @SWG\Response(response=200, description="HistoricAnnouncement announcement found",
     *     @SWG\Schema(ref="#/definitions/HistoricAnnouncementResponse")
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
     * @throws NotFoundHttpException
     */
    public function getHistoricAnnouncementAction(int $id, ParamFetcher $paramFetcher);


    /**
     * Searches historic announcements by criteria
     *
     * @SWG\Post(path="/history/announcements/searches", operationId="rest_search_historic_announcements",
     *   tags={ "History - announcements" },
     *
     *   @SWG\Parameter(
     *     in="body", name="filter", required=true,
     *     description="The historic announcement filter data",
     *
     *     @SWG\Schema(ref="#/definitions/HistoricAnnouncementFilter")
     *   ),
     *
     *   @SWG\Response(response=200, description="HistoricAnnouncement announcements found",
     *     @SWG\Schema(ref="#/definitions/HistoricAnnouncementListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found"),
     *   @SWG\Response(response=400, description="Bad request")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchHistoricAnnouncementsAction(Request $request);

}