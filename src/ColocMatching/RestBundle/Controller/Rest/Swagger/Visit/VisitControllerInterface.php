<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\Visit;

use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Tag(name="Visits")
 */
interface VisitControllerInterface {

    /**
     * Lists the visits with pagination
     *
     * @SWG\Get(path="/visits", operationId="rest_get_visits", tags={ "Visits" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(
     *     in="query", name="page", type="integer", default=1, minimum=0,
     *     description="The page of the paginated search"),
     *   @SWG\Parameter(
     *     in="query", name="size", type="integer", default=20, minimum=1,
     *     description="The number of results to return"),
     *   @SWG\Parameter(
     *     in="query", name="sort", type="string", default="id",
     *     description="The name of the attribute to order the results"),
     *   @SWG\Parameter(
     *     in="query", name="order", type="string", enum={"asc", "desc"}, default="asc",
     *     description="The sort direction ('asc' for ascending sort, 'desc' for descending sort)"),
     *   @SWG\Parameter(
     *     in="query", name="type", type="string", enum={ "announcement", "group", "user" }, required=true,
     *     description="The visitable type"),
     *   @SWG\Response(
     *     response=200, description="Visits found", @SWG\Schema(ref="#/definitions/UserVisitPageResponse")),
     *   @SWG\Response(response=206, description="Partial content found"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access")
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getVisitsAction(ParamFetcher $paramFetcher);


    /**
     * Searches visits with filtering
     *
     * @SWG\Post(path="/visits/searches", operationId="rest_search_visits", tags={ "Visits" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(
     *     in="query", name="type", type="string", enum={ "announcement", "group", "user" }, required=true,
     *     description="The visitable type"),
     *   @SWG\Parameter(
     *     in="body", name="filter", required=true, description="The visit filter data",
     *     @SWG\Schema(ref="#/definitions/VisitFilter")),
     *   @SWG\Response(
     *     response=200, description="Visits found", @SWG\Schema(ref="#/definitions/UserVisitPageResponse")),
     *   @SWG\Response(response=206, description="Partial content found"),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchVisitsAction(Request $request);
}