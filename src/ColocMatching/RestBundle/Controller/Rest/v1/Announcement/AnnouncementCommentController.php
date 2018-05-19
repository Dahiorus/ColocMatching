<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\Announcement\CommentDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Announcement\CommentDtoForm;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\Order;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\PageRequest;
use ColocMatching\CoreBundle\Security\User\TokenEncoderInterface;
use ColocMatching\CoreBundle\Validator\FormValidator;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use ColocMatching\RestBundle\Security\Authorization\Voter\AnnouncementVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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

    /** @var FormValidator */
    private $formValidator;

    /** @var TokenEncoderInterface */
    private $tokenEncoder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, AnnouncementDtoManagerInterface $announcementManager,
        FormValidator $formValidator, TokenEncoderInterface $tokenEncoder)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->announcementManager = $announcementManager;
        $this->formValidator = $formValidator;
        $this->tokenEncoder = $tokenEncoder;
    }


    /**
     * Lists an announcement comments
     *
     * @Rest\Get(name="rest_get_announcement_comments")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="10")
     *
     * @Operation(tags={ "Announcement - comments" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Response(response=200, description="Announcement comments found"),
     *   @SWG\Response(response=206, description="Partial content"),
     *   @SWG\Response(response=404, description="No announcement found"),
     * )
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

        $this->logger->debug("Listing an announcement comments", array ("id" => $id, "page" => $page, "size" => $size));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        $pageable = new PageRequest($page, $size, array ("createdAt" => Order::DESC));
        /** @var PageResponse $response */
        $response = new PageResponse(
            $this->announcementManager->getComments($announcement, $pageable),
            "rest_get_announcement_comments", array ("id" => $id, "page" => $page, "size" => $size),
            $pageable, $this->announcementManager->countComments($announcement));

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
     * @Operation(tags={ "Announcement - comments" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(name="comment", in="body", required=true, description="The comment to create",
     *     @Model(type=CommentDtoForm::class)),
     *   @SWG\Response(response=201, description="Announcement comment created", @Model(type=CommentDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No announcement found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
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
        $this->logger->debug("Creating a comment for an announcement",
            array ("id" => $id, "postParams" => $request->request->all()));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        $this->evaluateUserAccess(AnnouncementVoter::COMMENT, $announcement);
        /** @var UserDto $author */
        $author = $this->tokenEncoder->decode($request);
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
     * @Operation(tags={ "Announcement - comments" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(in="path", name="commentId", type="integer", required=true,
     *     description="The comment identifier"),
     *   @SWG\Response(response=200, description="Comment deleted"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No announcement found")
     * )
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
        $this->logger->debug("Deleting a comment from an announcement", array ("id" => $id, "commentId" => $commentId));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        $this->evaluateUserAccess(AnnouncementVoter::DELETE_COMMENT, $announcement);

        $comment = new CommentDto();
        $comment->setId($commentId);

        $this->announcementManager->deleteComment($announcement, $comment);

        $this->logger->info("Announcement comment deleted", array ("comment" => $comment));

        return new JsonResponse("Comment deleted");
    }
}
