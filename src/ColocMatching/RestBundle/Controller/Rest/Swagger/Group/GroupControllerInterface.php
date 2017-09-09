<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\Group;

use FOS\RestBundle\Request\ParamFetcher;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @SWG\Definition(
 *   definition="GroupListResponse",
 *   allOf={
 *     {"$ref"="#/definitions/PageResponse"}
 *   },
 *   @SWG\Property(property="content", type="array",
 *     @SWG\Items(ref="#/definitions/Group")
 * ))
 *
 * @SWG\Definition(
 *   definition="GroupResponse",
 *   allOf={
 *     {"$ref"="#/definitions/EntityResponse"}
 *   },
 *   @SWG\Property(property="content", ref="#/definitions/Group")
 * )
 *
 * @SWG\Tag(name="Groups")
 *
 * @author Dahiorus
 */
interface GroupControllerInterface {

    /**
     * Lists groups or specified fields with pagination
     *
     * @SWG\Get(path="/groups", operationId="rest_get_groups",
     *   tags={ "Groups" },
     *
     *   @SWG\Parameter(
     *     in="query", name="page", type="integer", default=1, minimum=1,
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
     *   @SWG\Response(response=200, description="Groups found",
     *     @SWG\Schema(ref="#/definitions/GroupListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found")
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getGroupsAction(ParamFetcher $paramFetcher);


    /**
     * Creates a new group for the authenticated user
     *
     * @SWG\Post(path="/groups", operationId="rest_create_group",
     *   tags={ "Groups" },
     *
     *   @SWG\Parameter(
     *     in="body", name="group", required=true,
     *     description="The data to post",
     *
     *     @SWG\Schema(ref="#/definitions/Group")
     *   ),
     *
     *   @SWG\Response(response=201, description="Group created",
     *     @SWG\Schema(ref="#/definitions/GroupResponse")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=422, description="Cannot recreate a group")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JWTDecodeFailureException
     * @throws UnprocessableEntityHttpException
     */
    public function createGroupAction(Request $request);


    /**
     * Gets an existing group or its fields
     *
     * @SWG\Get(path="/groups/{id}", operationId="rest_get_group",
     *   tags={ "Groups" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="fields", type="array",
     *     description="The fields to return",
     *     uniqueItems=true, collectionFormat="csv",
     *
     *     @SWG\Items(type="string")
     *   ),
     *
     *   @SWG\Response(response=200, description="Group found",
     *     @SWG\Schema(ref="#/definitions/GroupResponse")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No group found")
     * )
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function getGroupAction(int $id, ParamFetcher $paramFetcher);


    /**
     * Updates an existing group
     *
     * @SWG\Put(path="/groups/{id}", operationId="rest_update_group",
     *   tags={ "Groups" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="user", required=true,
     *     description="The data to put",
     *
     *     @SWG\Schema(ref="#/definitions/Group")
     *   ),
     *
     *   @SWG\Response(response=200, description="Group updated",
     *     @SWG\Schema(ref="#/definitions/GroupResponse")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No group found")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function updateGroupAction(int $id, Request $request);


    /**
     * Updates (partial) an existing announcement
     *
     * @SWG\Patch(path="/groups/{id}", operationId="rest_patch_group",
     *   tags={ "Groups" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="group", required=true,
     *     description="The data to patch",
     *
     *     @SWG\Schema(ref="#/definitions/Group")
     *   ),
     *
     *   @SWG\Response(response=200, description="Group updated",
     *     @SWG\Schema(ref="#/definitions/GroupResponse")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No group found")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function patchGroupAction(int $id, Request $request);


    /**
     * Deletes an existing group
     *
     * @SWG\Delete(path="/groups/{id}", operationId="rest_delete_group",
     *   tags={ "Groups" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Group deleted"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteGroupAction(int $id);


    /**
     * Searches groups by criteria
     *
     * @SWG\Post(path="/groups/searches", operationId="rest_search_groups",
     *   tags={ "Groups" },
     *
     *   @SWG\Parameter(
     *     in="body", name="filter", required=true,
     *     description="The group filter data",
     *
     *     @SWG\Schema(ref="#/definitions/GroupFilter")
     *   ),
     *
     *   @SWG\Response(response=200, description="Groups found",
     *     @SWG\Schema(ref="#/definitions/GroupListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found"),
     *   @SWG\Response(response=400, description="Bad request")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchGroupsAction(Request $request);


    /**
     * Gets all members of an existing group
     *
     * @SWG\Get(path="/groups/{id}/members", operationId="rest_get_group_members",
     *   tags={ "Groups" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Group found and members returned",
     *     @SWG\Schema(title="Members", type="array",
     *       @SWG\Items(title="User", ref="#/definitions/User")
     *   )),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No group found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function getMembersAction(int $id);


    /**
     * Removes a member from an existing group
     *
     * @SWG\Delete(path="/groups/{id}/members/{userId}", operationId="rest_remove_group_member",
     *   tags={ "Groups" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
     *   ),
     *   @SWG\Parameter(
     *     in="path", name="userId", type="integer", required=true,
     *     description="The member id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Group found and member removed"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No Announcement found"),
     *   @SWG\Response(response=422, description="Connected user is not the creator of the group")
     * )
     *
     * @param int $id
     * @param int $userId
     * @param Request $request
     *
     * @return JsonResponse
     * @throws UnprocessableEntityHttpException
     */
    public function removeMemberAction(int $id, int $userId, Request $request);

}