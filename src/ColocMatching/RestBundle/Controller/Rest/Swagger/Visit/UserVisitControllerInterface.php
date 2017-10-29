<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\Visit;

use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Exception\VisitNotFoundException;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Definition(
 *   definition="UserVisitPageResponse", allOf={ @SWG\Schema(ref="#/definitions/PageResponse")},
 *   @SWG\Property(property="content", type="array", @SWG\Items(ref="#/definitions/UserVisit"))
 * )
 * @SWG\Tag(name="Visits - users", description="Visits on users")
 */
interface UserVisitControllerInterface {

    /**
     * Lists the visits on one user with pagination
     *
     * @SWG\Get(path="/users/{id}/visits", operationId="rest_get_user_visits", tags={ "Visits - users" },
     *   security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
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
     *   @SWG\Response(
     *     response=200, description="Visits found", @SWG\Schema(ref="#/definitions/UserVisitPageResponse")),
     *   @SWG\Response(response=206, description="Partial content found"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access")
     * )
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getVisitsAction(int $id, ParamFetcher $paramFetcher);


    /**
     * Gets an existing visit on a user
     *
     * @SWG\Get(path="/users/{id}/visits/{visitId}", operationId="rest_get_user_visit", tags={ "Visits - users" },
     *   security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(in="path", name="visitId", type="integer", required=true, description="The visit identifier"),
     *   @SWG\Response(response=200, description="Visit found", @SWG\Schema(ref="#/definitions/UserVisit")),
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
     * @throws UserNotFoundException
     */
    public function getVisitAction(int $id, int $visitId);


    /**
     * Searches visits on a user by criteria
     *
     * @SWG\Post(path="/users/{id}/visits/searches", operationId="rest_search_user_visits", tags={ "Visits - users" },
     *   security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(
     *     in="body", name="filter", required=true, description="The visit filter data",
     *     @SWG\Schema(ref="#/definitions/VisitFilter")),
     *
     *   @SWG\Response(
     *     response=200, description="Visits found", @SWG\Schema(ref="#/definitions/UserVisitPageResponse")),
     *   @SWG\Response(response=206, description="Partial content found"),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchVisitsAction(int $id, Request $request);

}