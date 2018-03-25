<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\Announcement\CommentDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\FilterFactory;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Security\User\TokenEncoderInterface;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Response\ResponseFactory;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for resource /announcements/{id}/comments
 *
 * @Rest\Route(path="/announcements/{id}/comments", requirements={ "id" = "\d+" },
 *   service="coloc_matching.rest.announcement_comment_controller")
 *
 * @author Dahiorus
 */
class AnnouncementCommentController extends AbstractRestController
{
    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var FilterFactory */
    private $filterBuilder;

    /** @var ResponseFactory */
    private $responseBuilder;

    /** @var TokenEncoderInterface */
    private $tokenEncoder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AnnouncementDtoManagerInterface $announcementManager, FilterFactory $filterBuilder,
        ResponseFactory $responseBuilder, TokenEncoderInterface $tokenEncoder)
    {
        parent::__construct($logger, $serializer);

        $this->announcementManager = $announcementManager;
        $this->filterBuilder = $filterBuilder;
        $this->responseBuilder = $responseBuilder;
        $this->tokenEncoder = $tokenEncoder;
    }


    /**
     * Gets comments of an announcement with pagination
     *
     * @Rest\Get(name="rest_get_announcement_comments")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="10")
     *
     * @param int $id
     * @param ParamFetcher $fetcher
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getCommentsAction(int $id, ParamFetcher $fetcher)
    {
        $page = $fetcher->get("page", true);
        $size = $fetcher->get("size", true);

        $this->logger->info("Listing an announcement comments",
            array ("id" => $id, "pageable" => array ("page" => $page, "size" => $size)));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        /** @var PageableFilter $filter */
        $filter = $this->filterBuilder->createPageableFilter($page, $size, PageableFilter::ORDER_DESC);
        /** @var CommentDto[] $comments */
        $comments = $this->announcementManager->getComments($announcement, $filter);

        /** @var PageResponse $response */
        $response = $this->responseBuilder->createPageResponse($comments,
            $this->announcementManager->countComments($announcement), $filter);

        $this->logger->info("Listing an announcement comments - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            $response->hasNext() ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Create a comment for an announcement with the authenticated user as the author
     *
     * @Rest\Post(name="rest_create_announcement_comment")
     * @Security(expression="has_role('ROLE_SEARCH')")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws InvalidFormException
     */
    public function createCommentAction(int $id, Request $request)
    {
        $this->logger->info("Creating a comment for an announcement",
            array ("id" => $id, "postParams" => $request->request->all()));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        /** @var UserDto $author */
        $author = $this->tokenEncoder->decode($request);

        $this->evaluateUserAccess(!empty(array_filter($this->announcementManager->getCandidates($announcement),
            function (UserDto $c) use ($author) {
                return $c->getId() == $author->getId();
            })), "This user cannot comment the announcement");

        /** @var CommentDto $comment */
        $comment = $this->announcementManager->createComment($announcement, $author, $request->request->all());

        $this->logger->info("Comment created", array ("response" => $comment));

        return $this->buildJsonResponse($comment, Response::HTTP_CREATED);
    }


    /**
     * Deletes a comment of an announcement
     *
     * @Rest\Delete("/{commentId}", name="rest_delete_announcement_comment", requirements={"commentId"="\d+"})
     *
     * @param int $id
     * @param int $commentId
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function deleteCommentAction(int $id, int $commentId)
    {
        $this->logger->info("Deleting a comment from an announcement",
            array ("id" => $id, "commentId" => $commentId));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);

        $comment = new CommentDto();
        $comment->setId($commentId);

        $this->announcementManager->deleteComment($announcement, $comment);

        return new JsonResponse("Comment deleted");
    }
}
