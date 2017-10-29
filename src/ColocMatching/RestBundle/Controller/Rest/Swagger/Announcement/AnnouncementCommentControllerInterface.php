<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\Announcement;

use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\CommentNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @SWG\Definition(
 *   definition="CommentPageResponse", allOf={ @SWG\Schema(ref="#/definitions/PageResponse") },
 *   @SWG\Property(property="content", type="array", @SWG\Items(ref="#/definitions/Comment"))
 * )
 * @SWG\Tag(name="Announcements - comments", description="Comments of an announcement")
 *
 * @author Dahiorus
 */
interface AnnouncementCommentControllerInterface {

    /**
     * Gets the comments of an announcement with pagination
     *
     * @SWG\Get(path="/announcements/{id}/comments", operationId="rest_get_announcement_comments",
     *   tags={ "Announcements - comments" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(
     *     in="query", name="page", type="integer", default=1, minimum=0,
     *     description="The page of the paginated search"),
     *   @SWG\Parameter(
     *     in="query", name="size", type="integer", default=10, minimum=1,
     *     description="The number of results to return"),
     *   @SWG\Response(
     *     response=200, description="Announcement found and comments returned",
     *     @SWG\Schema(ref="#/definitions/CommentPageResponse")),
     *   @SWG\Response(response=206, description="Partial content"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No announcement found")
     * )
     *
     * @param int $id
     * @param ParamFetcher $fetcher
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function getCommentsAction(int $id, ParamFetcher $fetcher);


    /**
     * Creates a comment for an announcement
     *
     * @SWG\Post(path="/announcements/{id}/comments", operationId="rest_create_announcement_comment",
     *   tags={ "Announcements - comments" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(
     *     in="body", name="comment", required=true, description="The data to post",
     *     @SWG\Schema(ref="#/definitions/Comment")),
     *   @SWG\Response(response=201, description="Comment created", @SWG\Schema(ref="#/definitions/Comment")),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No announcement found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     * @throws AccessDeniedException
     * @throws InvalidFormException
     */
    public function createCommentAction(int $id, Request $request);


    /**
     * Gets a comment of an announcement
     *
     * @SWG\Get(path="/announcements/{id}/comments/{commentId}", operationId="rest_get_announcement_comment",
     *   tags={ "Announcements - comments" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(
     *     in="path", name="commentId", type="integer", required=true, description="The comment identifier"),
     *   @SWG\Response(
     *     response=200, description="Announcement found and comment returned",
     *     @SWG\Schema(ref="#/definitions/Comment")),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No announcement nor comment found")
     * )
     *
     * @param int $id
     * @param int $commentId
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     * @throws CommentNotFoundException
     */
    public function getCommentAction(int $id, int $commentId);


    /**
     * Deletes a comment of an announcement
     *
     * @SWG\Delete(path="/announcements/{id}/comments/{commentId}", operationId="rest_delete_announcement_comment",
     *   tags={ "Announcements - comments" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(
     *     in="path", name="commentId", type="integer", required=true, description="The comment identifier"),
     *   @SWG\Response(response=200, description="Comment deleted"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No announcement found")
     * )
     *
     * @param int $id
     * @param int $commentId
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function deleteCommentAction(int $id, int $commentId);
}