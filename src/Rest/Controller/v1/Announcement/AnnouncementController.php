<?php

namespace App\Rest\Controller\v1\Announcement;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\User\UserDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Form\Type\Announcement\AnnouncementDtoForm;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Repository\Filter\AnnouncementFilter;
use App\Core\Repository\Filter\Converter\StringConverterInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Security\User\TokenEncoderInterface;
use App\Rest\Controller\Response\Announcement\AnnouncementPageResponse;
use App\Rest\Controller\Response\PageResponse;
use App\Rest\Controller\v1\AbstractRestController;
use App\Rest\Event\DeleteAnnouncementEvent;
use App\Rest\Event\Events;
use App\Rest\Listener\EventDispatcherVisitor;
use App\Rest\Security\Authorization\Voter\AnnouncementVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for resource /announcements
 *
 * @Rest\Route(path="/announcements")
 *
 * @author Dahiorus
 */
class AnnouncementController extends AbstractRestController
{
    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var RouterInterface */
    private $router;

    /** @var EventDispatcherVisitor */
    private $visitVisitor;

    /** @var TokenEncoderInterface */
    private $tokenEncoder;

    /** @var StringConverterInterface */
    private $stringConverter;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, AnnouncementDtoManagerInterface $announcementManager,
        EventDispatcherInterface $eventDispatcher, RouterInterface $router, EventDispatcherVisitor $visitVisitor,
        TokenEncoderInterface $tokenEncoder, StringConverterInterface $stringConverter)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->announcementManager = $announcementManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
        $this->visitVisitor = $visitVisitor;
        $this->tokenEncoder = $tokenEncoder;
        $this->stringConverter = $stringConverter;
    }


    /**
     * Lists announcements
     *
     * @Rest\Get(name="rest_get_announcements")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters (prefix with '-' to DESC sort)",
     *   default="-createdAt")
     * @Rest\QueryParam(
     *   name="q", nullable=true,
     *   description="Search query to filter results (csv), parameters are in the form 'name=value'")
     *
     * @Operation(tags={ "Announcement" },
     *   @SWG\Response(response=200, description="Announcements found", @Model(type=AnnouncementPageResponse::class)),
     *   @SWG\Response(response=400, description="Invalid search query filter")
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getAnnouncementsAction(ParamFetcher $paramFetcher)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);
        $filter = $this->extractQueryFilter(AnnouncementFilter::class, $paramFetcher, $this->stringConverter);
        $pageable = PageRequest::create($parameters);

        $this->logger->debug("Listing announcements", array_merge($parameters, ["filter" => $filter]));

        $result = empty($filter) ? $this->announcementManager->list($pageable)
            : $this->announcementManager->search($filter, $pageable);
        $response = new PageResponse($result, "rest_get_announcements", $paramFetcher->all());

        $this->logger->info("Listing announcements - result information", array ("response" => $response));

        return $this->buildJsonResponse($response);
    }


    /**
     * Creates a new announcement for the authenticated user
     *
     * @Rest\Post(name="rest_create_announcement")
     * @Security(expression="is_granted('ROLE_PROPOSAL')")
     *
     * @Operation(tags={ "Announcement" },
     *   @SWG\Parameter(name="user", in="body", required=true, description="The announcement to create",
     *     @Model(type=AnnouncementDtoForm::class)),
     *   @SWG\Response(response=201, description="Announcement created", @Model(type=AnnouncementDto::class)),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function createAnnouncementAction(Request $request)
    {
        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);

        $this->logger->debug("Posting a new announcement",
            array ("user" => $user, "postParams" => $request->request->all()));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->create($user, $request->request->all());

        $this->logger->info("Announcement created", array ("response" => $announcement));

        return $this->buildJsonResponse($announcement,
            Response::HTTP_CREATED, array ("Location" => $this->router->generate("rest_get_announcement",
                array ("id" => $announcement->getId()), Router::ABSOLUTE_URL)));
    }


    /**
     * Gets an existing announcement
     *
     * @Rest\Get(path="/{id}", name="rest_get_announcement", requirements={"id"="\d+"})
     *
     * @Operation(tags={ "Announcement" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Response(response=200, description="Announcement found", @Model(type=AnnouncementDto::class)),
     *   @SWG\Response(response=404, description="No announcement found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function getAnnouncementAction(int $id)
    {
        $this->logger->debug("Getting an existing announcement", array ("id" => $id));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);

        $this->logger->info("One announcement found", array ("response" => $announcement));

        $this->visitVisitor->visit($announcement);

        return $this->buildJsonResponse($announcement);
    }


    /**
     * Updates an existing announcement
     *
     * @Rest\Put(path="/{id}", name="rest_update_announcement", requirements={"id"="\d+"})
     * @Rest\Patch(path="/{id}", name="rest_patch_announcement", requirements={"id"="\d+"})
     *
     * @Operation(tags={ "Announcement" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(name="announcement", in="body", required=true, description="The announcement to update",
     *     @Model(type=AnnouncementDtoForm::class)),
     *   @SWG\Response(response=200, description="Announcement updated", @Model(type=AnnouncementDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No announcement found"),
     *   @SWG\Response(response=400, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function updateAnnouncementAction(int $id, Request $request)
    {
        $this->logger->debug("Updating an announcement", array ("id" => $id, "params" => $request->request->all()));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        $this->evaluateUserAccess(AnnouncementVoter::UPDATE, $announcement);

        $announcement = $this->announcementManager->update(
            $announcement, $request->request->all(), $request->isMethod("PUT"));

        $this->logger->info("Announcement updated", array ("response" => $announcement));

        return $this->buildJsonResponse($announcement);
    }


    /**
     * Deletes an existing announcement
     *
     * @Rest\Delete(path="/{id}", name="rest_delete_announcement", requirements={"id"="\d+"})
     *
     * @Operation(tags={ "Announcement" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Response(response=204, description="Announcement deleted"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function deleteAnnouncementAction(int $id)
    {
        $this->logger->info("Deleting an existing announcement", array ("id" => $id));

        try
        {
            /** @var AnnouncementDto $announcement */
            $announcement = $this->announcementManager->read($id);
            $this->evaluateUserAccess(AnnouncementVoter::DELETE, $announcement);
            $this->eventDispatcher->dispatch(Events::DELETE_ANNOUNCEMENT_EVENT,
                new DeleteAnnouncementEvent($announcement->getId()));
            $this->announcementManager->delete($announcement);

            $this->logger->info("Announcement deleted", array ("announcement" => $announcement));
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->warning("Trying to delete an non existing announcement", array ("id" => $id));
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    /**
     * Gets all candidates of an existing announcement
     *
     * @Rest\Get(path="/{id}/candidates", name="rest_get_announcement_candidates",
     *   requirements={"id"="\d+"})
     * @Security(expression="is_granted('ROLE_USER')")
     *
     * @Operation(tags={ "Announcement" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Response(
     *     response=200, description="Announcement candidates found",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=UserDto::class))) ),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=404, description="No announcement found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getCandidatesAction(int $id)
    {
        $this->logger->debug("Getting all candidates of an existing announcement", array ("id" => $id));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        /** @var UserDto[] $candidates */
        $candidates = $this->announcementManager->getCandidates($announcement);

        $this->logger->info("Announcement candidates found", array ("candidates" => $candidates));

        return $this->buildJsonResponse($candidates);
    }


    /**
     * Removes a candidate from an existing announcement
     *
     * @Rest\Delete(path="/{id}/candidates/{userId}", name="rest_remove_announcement_candidate",
     *   requirements={"id"="\d+"})
     * @Security("is_granted('ROLE_USER')")
     *
     * @Operation(tags={ "Announcement" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(in="path", name="userId", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(response=204, description="Announcement candidate removed"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No announcement found")
     * )
     *
     * @param int $id
     * @param int $userId
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function removeCandidateAction(int $id, int $userId)
    {
        $this->logger->debug("Removing a candidate from an existing announcement",
            array ("id" => $id, "userId" => $userId));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        $this->evaluateUserAccess(AnnouncementVoter::REMOVE_CANDIDATE,
            ["announcement" => $announcement, "targetId" => $userId]);

        $candidate = new UserDto();
        $candidate->setId($userId);

        try
        {
            $this->announcementManager->removeCandidate($announcement, $candidate);

            $this->logger->info("Announcement candidate removed", array ("candidate" => $candidate));
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->warning("Trying to remove a non existing candidate from an announcement",
                array ("announcement" => $announcement, "exception" => $e));
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
