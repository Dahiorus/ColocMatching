<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\Visit;

use ColocMatching\CoreBundle\Exception\VisitNotFoundException;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Definition(
 *   definition="GroupVisitListResponse",
 *   allOf={
 *     {"$ref"="#/definitions/PageResponse"}
 *   },
 *   @SWG\Property(property="data", type="array",
 *     @SWG\Items(ref="#/definitions/GroupVisit")
 * ))
 *
 * @SWG\Definition(
 *   definition="GroupVisitResponse",
 *   allOf={
 *     {"$ref"="#/definitions/EntityResponse"}
 *   },
 *   @SWG\Property(property="content", ref="#/definitions/GroupVisit")
 * )
 *
 * @SWG\Tag(name="Groups - visits", description="Visits on groups")
 */
interface GroupVisitControllerInterface {

    /**
     * Lists the visits on a group with pagination
     *
     * @SWG\Get(path="/groups/{id}/visits", operationId="rest_get_group_visits",
     *   tags={ "Groups - visits" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
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
     *     @SWG\Schema(ref="#/definitions/GroupVisitListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found")
     * )
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getVisitsAction(int $id, ParamFetcher $paramFetcher);


    /**
     * Gets an existing visit on a group
     *
     * @SWG\Get(path="/groups/{id}/visits/{visitId}", operationId="rest_get_group_visit",
     *   tags={ "Groups - visits" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
     *   ),
     *   @SWG\Parameter(
     *     in="path", name="visitId", type="integer", required=true,
     *     description="The visit id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Visit found",
     *     @SWG\Schema(ref="#/definitions/GroupVisitResponse")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No visit found")
     * )
     *
     * @param int $id
     * @param int $visitId
     *
     * @return JsonResponse
     * @throws VisitNotFoundException
     */
    public function getVisitAction(int $id, int $visitId);


    /**
     * Searches visits on groups by criteria
     *
     * @SWG\Post(path="/groups/{id}/visits/searches", operationId="rest_search_groups_visits",
     *   tags={ "Groups - visits" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="filter", required=true,
     *     description="The visit filter data",
     *
     *     @SWG\Schema(ref="#/definitions/VisitFilter")
     *   ),
     *
     *   @SWG\Response(response=200, description="groups found",
     *     @SWG\Schema(ref="#/definitions/GroupVisitListResponse")
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