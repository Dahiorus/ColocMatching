<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\User;

use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @SWG\Definition(
 *   definition="PrivateConversationPageResponse", allOf={ @SWG\Schema(ref="#/definitions/PageResponse") },
 *   @SWG\Property(property="content", type="array", @SWG\Items(ref="#/definitions/PrivateConversation")))
 * @SWG\Tag(name="Me", description="Self service")
 */
interface SelfControllerInterface {

    /**
     * Gets the authenticated user
     *
     * @SWG\Get(path="/me", operationId="rest_get_me", tags={"Me"}, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Response(response=200, description="User found", @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=401, description="Unauthorized access")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getSelfAction(Request $request);


    /**
     * Updates the authenticated user
     *
     * @SWG\Put(path="/me", operationId="rest_update_me", tags={"Me"}, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(
     *     in="body", name="user", required=true, description="The data to put", @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=200, description="User updated", @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateSelfAction(Request $request);


    /**
     * Updates (partial) the authenticated user
     *
     * @SWG\Patch(path="/me", operationId="rest_patch_me", tags={"Me"}, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(
     *     in="body", name="user", required=true, description="The data to patch",
     *     @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=200, description="User updated", @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=422, description="Validation error")
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
     * @SWG\Patch(path="/me/status", operationId="rest_patch_me_status", tags={ "Me" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(
     *     in="body", name="status", required=true, description="The status to set to the user",
     *     @SWG\Schema(
     *       @SWG\Property(property="value", type="string", description="The value of the status",
     *         enum={"enabled", "vacation"}, default="enabled"), required={ "value" })),
     *   @SWG\Response(response=200, description="Status updated", @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=400, description="Unknown status to set"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws BadRequestHttpException
     */
    public function updateSelfStatusAction(Request $request);


    /**
     * Updates the password of the authenticated user
     *
     * @SWG\Post(path="/me/password", operationId="rest_patch_me_password", tags={ "Me" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(
     *     in="body", name="value", required=true, description="The data to post",
     *     @SWG\Schema(ref="#/definitions/EditPassword")),
     *   @SWG\Response(response=200, description="Password updated", @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws BadRequestHttpException
     */
    public function updateSelfPasswordAction(Request $request);


    /**
     * Lists the visits done by the authenticated user with pagination
     *
     * @SWG\Get(path="/me/visits", operationId="rest_get_me_visits", tags={ "Me" }, security={
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
     *     description="The visitable type", default="announcement"),
     *   @SWG\Response(
     *     response=200, description="Visits found", @SWG\Schema(ref="#/definitions/AnnouncementVisitPageResponse")),
     *   @SWG\Response(response=206, description="Partial content found"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param ParamFetcher $fetcher
     *
     * @return JsonResponse
     */
    public function getSelfVisitsAction(ParamFetcher $fetcher);


    /**
     * Lists historic announcements or specified fields of the authenticated user with pagination
     *
     * @SWG\Get(path="/me/history/announcements", operationId="rest_get_me_historic_announcements", tags={ "Me" },
     *   security={
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
     *     in="query", name="fields", type="array", description="The fields to return for each result",
     *     uniqueItems=true, collectionFormat="csv", @SWG\Items(type="string")),
     *   @SWG\Response(response=200, description="Historic announcements found",
     *     @SWG\Schema(ref="#/definitions/HistoricAnnouncementPageResponse")),
     *   @SWG\Response(response=206, description="Partial content found"),
     *   @SWG\Response(response=401, description="Unauthorized access")
     * )
     *
     * @param ParamFetcher $fetcher
     *
     * @return JsonResponse
     */
    public function getSelfHistoricAnnouncementsAction(ParamFetcher $fetcher);


    /**
     * Lists private conversations of the authenticated user with pagination
     *
     * @SWG\Get(path="/me/conversations", operationId="rest_get_me_private_conversations", tags={ "Me" },
     *   security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(
     *     in="query", name="page", type="integer", default=1, minimum=0,
     *     description="The page of the paginated search"),
     *   @SWG\Parameter(
     *     in="query", name="size", type="integer", default=10, minimum=1,
     *     description="The number of results to return"),
     *   @SWG\Parameter(
     *     in="query", name="sort", type="string", default="lastUpdate",
     *     description="The name of the attribute to order the results"),
     *   @SWG\Parameter(
     *     in="query", name="order", type="string", enum={"asc", "desc"}, default="desc",
     *     description="The sort direction ('asc' for ascending sort, 'desc' for descending sort)"),
     *   @SWG\Response(response=200, description="Private conversations found",
     *     @SWG\Schema(ref="#/definitions/PrivateConversationPageResponse")),
     *   @SWG\Response(response=206, description="Partial content found"),
     *   @SWG\Response(response=401, description="Unauthorized access")
     * )
     *
     * @param ParamFetcher $fetcher
     *
     * @return mixed
     */
    public function getSelfPrivateConversations(ParamFetcher $fetcher);

}