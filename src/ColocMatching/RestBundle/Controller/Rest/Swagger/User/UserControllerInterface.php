<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\User;

use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Definition(
 *   definition="UserPageResponse", allOf={ @SWG\Schema(ref="#/definitions/PageResponse") },
 *   @SWG\Property(property="content", type="array", @SWG\Items(ref="#/definitions/User"))
 * )
 * @SWG\Tag(name="Users")
 *
 * @author Dahiorus
 */
interface UserControllerInterface {

    /**
     * Lists users or fields with pagination
     *
     * @SWG\Get(path="/users", operationId="rest_get_users", tags={ "Users" },
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
     *
     *   @SWG\Response(response=200, description="Users found", @SWG\Schema(ref="#/definitions/UserPageResponse")),
     *   @SWG\Response(response=206, description="Partial content found")
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getUsersAction(ParamFetcher $paramFetcher);


    /**
     * Creates a new user
     *
     * @SWG\Post(path="/users", operationId="rest_create_user", tags={ "Users"},
     *   @SWG\Parameter(
     *     in="body", name="user", required=true, description="The data to post",
     *     @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=201, description="User created", @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createUserAction(Request $request);


    /**
     * Gets an existing user or its fields
     *
     * @SWG\Get(path="/users/{id}", operationId="rest_get_user", tags={ "Users" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(
     *     in="query", name="fields", type="array", description="The fields to return",
     *     uniqueItems=true, collectionFormat="csv", @SWG\Items(type="string")),
     *   @SWG\Response(response=200, description="User found", @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=404, description="No User found")
     * )
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getUserAction(int $id, ParamFetcher $paramFetcher);


    /**
     * Updates an existing user
     *
     * @SWG\Put(path="/users/{id}", operationId="rest_update_user", tags={ "Users" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(
     *     in="body", name="user", required=true, description="The data to put", @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=200, description="User updated", @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateUserAction(int $id, Request $request);


    /**
     * Updates (partial) an existing user
     *
     * @SWG\Patch(path="/users/{id}", operationId="rest_patch_user", tags={ "Users" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(
     *     in="body", name="user", required=true, description="The data to patch",
     *     @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=200, description="User updated", @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No User found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function patchUserAction(int $id, Request $request);


    /**
     * Deletes an existing user
     *
     * @SWG\Delete(path="/users/{id}", operationId="rest_delete_user", tags={ "Users" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(response=200, description="User deleted"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteUserAction(int $id);


    /**
     * Updates the status of a user
     *
     * @SWG\Patch(path="/users/{id}/status", operationId="rest_patch_user_status", tags={ "Users" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(
     *     in="body", name="status", required=true, description="The status to set to the user",
     *     @SWG\Schema(
     *       @SWG\Property(property="value", type="string", description="The value of the status",
     *         enum={"enabled", "vacation", "banned"}, default="enabled"), required={ "value" })),
     *   @SWG\Response(response=200, description="User status updated", @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=400, description="Unknown status to set"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="User not found")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     * @throws InvalidParameterException
     */
    public function updateStatusAction(int $id, Request $request);


    /**
     * Searches users by criteria
     *
     * @SWG\Post(path="/users/searches", operationId="rest_search_users", tags={ "Users" },
     *   @SWG\Parameter(
     *     in="body", name="filter", required=true, description="The user filter data",
     *     @SWG\Schema(ref="#/definitions/UserFilter")),
     *   @SWG\Response(response=200, description="Users found", @SWG\Schema(ref="#/definitions/UserPageResponse")),
     *   @SWG\Response(response=206, description="Partial content found"),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     */
    public function searchUsersAction(Request $request);

}
