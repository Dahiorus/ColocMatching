<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\Comment;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\CommentNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\RestBundle\Controller\Response\EntityResponse;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\Announcement\AnnouncementCommentControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * REST controller for resource /announcements/{id}/comments
 *
 * @Rest\Route(path="/announcements/{id}/comments", requirements={ "id" = "\d+" })
 *
 * @author Dahiorus
 */
class AnnouncementCommentController extends RestController implements AnnouncementCommentControllerInterface {

    /**
     * Gets comments of an announcement with pagination
     *
     * @Rest\Get("", name="rest_get_announcement_comments")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="10")
     *
     * @param int $id
     * @param ParamFetcher $fetcher
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function getCommentsAction(int $id, ParamFetcher $fetcher) {
        $page = $fetcher->get("page", true);
        $size = $fetcher->get("size", true);

        $this->get("logger")->info("Listing the comments of an announcement",
            array ("id" => $id, "pageable" => array ("page" => $page, "size" => $size)));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        /** @var Announcement $announcement */
        $announcement = $manager->read($id);
        /** @var PageableFilter $filter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($page, $size,
            PageableFilter::ORDER_DESC, "createdAt");
        /** @var array<Comment> $comments */
        $comments = $manager->getComments($announcement, $filter);

        /** @var PageResponse $response */
        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($comments,
            $announcement->getComments()->count(), $filter);

        $this->get("logger")->info("Listing the comments of an announcement - result information",
            array ("response" => $response));

        return $this->buildJsonResponse($response,
            $response->hasNext() ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Create a comment for an announcement with the authenticated user as the author
     *
     * @Rest\Post("", name="rest_create_announcement_comment")
     * @Security(expression="has_role('ROLE_SEARCH')")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     * @throws AccessDeniedException
     */
    public function createCommentAction(int $id, Request $request) {
        $this->get("logger")->info("Creating a comment for an announcement",
            array ("id" => $id, "request" => $request->request));

        /** @var AnnouncementManagerInterface $manager */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        /** @var Announcement $announcement */
        $announcement = $manager->read($id);
        /** @var User $author */
        $author = $this->extractUser($request);

        if (!$announcement->getCandidates()->contains($author)) {
            throw new AccessDeniedException("This user cannot comment the announcement");
        }

        try {
            /** @var Comment $comment */
            $comment = $manager->createComment($announcement, $author, $request->request->all());
            /** @var string $url */
            $url = sprintf("%s/%d", $request->getUri(), $comment->getId());
            /** @var EntityResponse $response */
            $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($comment);

            $this->get("logger")->info("Comment created", array ("response" => $response));

            return $this->buildJsonResponse($response, Response::HTTP_CREATED, array ("Location" => $url));
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while creating a comment for an announcement",
                array ("id" => $id, "request" => $request->request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }


    /**
     * Gets a comment of an announcement
     *
     * @Rest\Get("/{commentId}", name="rest_get_announcement_comment")
     *
     * @param int $id
     * @param int $commentId
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     * @throws CommentNotFoundException
     */
    public function getCommentAction(int $id, int $commentId) {
        $this->get("logger")->info("Getting a comment of an announcement",
            array ("id" => $id, "commentId" => $commentId));

        /** @var AnnouncementManagerInterface $manager */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        /** @var Announcement $announcement */
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


    /**
     * Deletes a comment of an announcement
     *
     * @Rest\Delete("/{commentId}", name="rest_delete_announcement_comment")
     *
     * @param int $id
     * @param int $commentId
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function deleteCommentAction(int $id, int $commentId) {
        $this->get("logger")->info("Deleting a comment from an announcement",
            array ("id" => $id, "commentId" => $commentId));

        /** @var AnnouncementManagerInterface $manager */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        /** @var Announcement $announcement */
        $announcement = $manager->read($id);

        $manager->deleteComment($announcement, $commentId);

        return new JsonResponse("Comment deleted");
    }
}
