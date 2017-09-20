<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\Announcement;

use ColocMatching\CoreBundle\Exception\CommentNotFoundException;
use ColocMatching\CoreBundle\Exception\HistoricAnnouncementNotFoundException;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;

interface HistoricAnnouncementCommentControllerInterface {

    /**
     * Gets the comments of an announcement with pagination
     *
     * @SWG\Get(path="/history/announcements/{id}/comments", operationId="rest_get_historic_announcement_comments",
     *   tags={ "History - announcements" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The historic announcement id"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="page", type="integer", default=1, minimum=0,
     *     description="The page of the paginated search"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="size", type="integer", default=10, minimum=1,
     *     description="The number of results to return"
     *   ),
     *
     *   @SWG\Response(response=200, description="Comments found",
     *     @SWG\Schema(ref="#/definitions/CommentListResponse")),
     *   @SWG\Response(response=206, description="Partial content"),
     *   @SWG\Response(response=404, description="No historic announcement found")
     * )
     *
     * @param int $id
     * @param ParamFetcher $fetcher
     *
     * @return JsonResponse
     * @throws HistoricAnnouncementNotFoundException
     */
    public function getCommentsAction(int $id, ParamFetcher $fetcher);


    /**
     * Gets a comment of an announcement
     *
     * @SWG\Get(path="/history/announcements/{id}/comments/{commentId}",
     *   operationId="rest_get_historic_announcement_comment", tags={ "History - announcements" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The historic announcement id"
     *   ),
     *   @SWG\Parameter(
     *     in="path", name="commentId", type="integer", required=true,
     *     description="The comment id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Comment found",
     *     @SWG\Schema(ref="#/definitions/CommentResponse")),
     *   @SWG\Response(response=404, description="No historic announcement nor comment found")
     * )
     *
     * @param int $id
     * @param int $commentId
     *
     * @return JsonResponse
     * @throws HistoricAnnouncementNotFoundException
     * @throws CommentNotFoundException
     */
    public function getCommentAction(int $id, int $commentId);
}