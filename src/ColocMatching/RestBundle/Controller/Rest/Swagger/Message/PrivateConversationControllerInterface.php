<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\Message;

use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Definition(
 *   definition="PrivateMessagePageResponse", allOf={ @SWG\Schema(ref="#/definitions/PageResponse") },
 *   @SWG\Property(property="content", type="array", @SWG\Items(ref="#/definitions/PrivateMessage"))
 * )
 * @SWG\Tag(name="Messages - private", description="Messages between 2 users")
 *
 * @author Dahiorus
 */
interface PrivateConversationControllerInterface {

    /**
     * Lists the messages between the authenticated user and the one specified by its identifier
     *
     * @SWG\Get(path="/users/{id}/messages", operationId="rest_get_private_messages", tags={ "Messages - private" },
     *   security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(
     *     in="query", name="page", type="integer", default=1, minimum=0,
     *     description="The page of the paginated search"),
     *   @SWG\Parameter(
     *     in="query", name="size", type="integer", default=10, minimum=1,
     *     description="The number of results to return"),
     *   @SWG\Response(
     *     response=200, description="User found and messages returned",
     *     @SWG\Schema(ref="#/definitions/PrivateMessagePageResponse")),
     *   @SWG\Response(response=206, description="Partial content"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No user found")
     * )
     *
     * @param int $id
     * @param ParamFetcher $fetcher
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function getMessagesAction(int $id, ParamFetcher $fetcher);


    /**
     * Posts a new message to a user with the authenticated user as the author of the message
     *
     * @SWG\Post(path="/users/{id}/messages", operationId="rest_post_private_message", tags={ "Messages - private" },
     *   security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(
     *     in="body", name="message", required=true, description="The data to post",
     *     @SWG\Schema(ref="#/definitions/PrivateMessage")),
     *   @SWG\Response(response=201, description="Message posted", @SWG\Schema(ref="#/definitions/PrivateMessage")),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No user found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     * @throws InvalidFormException
     */
    public function postMessageAction(int $id, Request $request);
}