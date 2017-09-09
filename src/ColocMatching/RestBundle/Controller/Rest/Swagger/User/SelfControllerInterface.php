<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\User;

use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @SWG\Tag(name="Me", description="Self service")
 */
interface SelfControllerInterface {

    /**
     * Gets the connected user
     *
     * @SWG\Get(path="/me", operationId="rest_get_me", tags={"Me"},
     *   @SWG\Response(response=200, description="User found",
     *     @SWG\Schema(ref="#/definitions/UserResponse")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getSelfAction(Request $request);


    /**
     * Updates the connected user
     *
     * @SWG\Put(path="/me", operationId="rest_update_me", tags={"Me"},
     *   @SWG\Parameter(
     *     in="body", name="user", required=true,
     *     description="The data to put",
     *
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *
     *   @SWG\Response(response=200, description="User updated",
     *     @SWG\Schema(ref="#/definitions/UserResponse")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateSelfAction(Request $request);


    /**
     * Updates (partial) the connected user
     *
     * @SWG\Patch(path="/me", operationId="rest_patch_me", tags={"Me"},
     *   @SWG\Parameter(
     *     in="body", name="user", required=true,
     *     description="The data to patch",
     *
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *
     *   @SWG\Response(response=200, description="User updated",
     *     @SWG\Schema(ref="#/definitions/UserResponse")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access")
     * )
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function patchSelfAction(Request $request);


    /**
     * Updates the status of the authenticated user
     *
     * @SWG\Patch(path="/me/status", operationId="rest_patch_me_status", tags={ "Me" },
     *
     *   @SWG\Parameter(
     *     in="body", name="status", required=true, description="The status to set to the user",
     *
     *     @SWG\Schema(
     *       @SWG\Property(property="value", type="string", required={ "value" }, description="The value of the status",
     *         enum={"enabled", "vacation"}))
     *   ),
     *
     *   @SWG\Response(response=200, description="Status updated",
     *     @SWG\Schema(ref="#/definitions/UserResponse")
     *   ),
     *   @SWG\Response(response=400, description="Unknown status to set")
     *
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws BadRequestHttpException
     */
    public function updateSelfStatusAction(Request $request);


    /**
     * Lists the visits done by the authenticated user with pagination
     *
     * @SWG\Get(path="/me/visits", operationId="rest_get_me_visits",
     *   tags={ "Me" },
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
     *     in="query", name="type", type="string", enum={ "announcement", "group", "user" }, required=true,
     *     description="The visitable type"
     *   ),
     *
     *   @SWG\Response(response=200, description="Visits found",
     *     @SWG\Schema(ref="#/definitions/AnnouncementVisitListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found")
     * )
     *
     * @param ParamFetcher $fetcher
     *
     * @return JsonResponse
     */
    public function getSelfVisitsAction(ParamFetcher $fetcher);

}