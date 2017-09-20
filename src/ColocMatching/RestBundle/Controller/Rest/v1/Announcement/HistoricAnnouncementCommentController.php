<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;
use ColocMatching\CoreBundle\Exception\CommentNotFoundException;
use ColocMatching\CoreBundle\Exception\HistoricAnnouncementNotFoundException;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\RestBundle\Controller\Response\EntityResponse;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\Announcement\HistoricAnnouncementCommentControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for resource /history/announcements/{id}/comments
 *
 * @Rest\Route(path="/history/announcements/{id}/comments", requirements={ "id" = "\d+" })
 *
 * @author Dahiorus
 */
class HistoricAnnouncementCommentController extends RestController implements
    HistoricAnnouncementCommentControllerInterface {

    /**
     * Gets comments of a historic announcement with pagination
     *
     * @Rest\Get("", name="rest_get_historic_announcement_comments")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="10")
     *
     * @param int $id
     * @param ParamFetcher $fetcher
     *
     * @return JsonResponse
     * @throws HistoricAnnouncementNotFoundException
     */
    public function getCommentsAction(int $id, ParamFetcher $fetcher) {
        $page = $fetcher->get("page", true);
        $size = $fetcher->get("size", true);

        $this->get("logger")->info("Listing the comments of a historic announcement",
            array ("id" => $id, "pageable" => array ("page" => $page, "size" => $size)));

        /** @var HistoricAnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.historic_announcement_manager");

        /** @var HistoricAnnouncement $announcement */
        $announcement = $manager->read($id);
        /** @var PageableFilter $filter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($page, $size,
            PageableFilter::ORDER_DESC, "createdAt");
        /** @var array<Comment> $comments */
        $comments = $manager->getComments($announcement, $filter);

        /** @var PageResponse $response */
        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($comments,
            $announcement->getComments()->count(), $filter);

        $this->get("logger")->info("Listing the comments of a historic announcement - result information",
            array ("response" => $response));

        return $this->buildJsonResponse($response,
            $response->hasNext() ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Gets a comment of a historic announcement
     *
     * @Rest\Get("/{commentId}", name="rest_get_historic_announcement_comment")
     *
     * @param int $id
     * @param int $commentId
     *
     * @return JsonResponse
     * @throws HistoricAnnouncementNotFoundException
     * @throws CommentNotFoundException
     */
    public function getCommentAction(int $id, int $commentId) {
        $this->get("logger")->info("Getting a comment of an announcement",
            array ("id" => $id, "commentId" => $commentId));

        /** @var HistoricAnnouncementManagerInterface $manager */
        $manager = $this->get("coloc_matching.core.historic_announcement_manager");

        /** @var HistoricAnnouncement $announcement */
        $announcement = $manager->read($id);
        /** @var EntityResponse $response */
        $response = null;

        foreach ($announcement->getComments() as $comment) {
            if ($comment->getId() == $commentId) {
                $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($comment);

                break;
            }
        }

        if (empty($response)) {
            throw new CommentNotFoundException("id", $commentId);
        }

        $this->get("logger")->info("Comment found", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }

}