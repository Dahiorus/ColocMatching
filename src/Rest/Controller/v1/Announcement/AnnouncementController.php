<?php

namespace App\Rest\Controller\v1\Announcement;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\User\UserDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidCreatorException;
use App\Core\Exception\InvalidFormException;
use App\Core\Form\Type\Announcement\AnnouncementDtoForm;
use App\Core\Form\Type\Filter\AnnouncementFilterForm;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Repository\Filter\AnnouncementFilter;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Security\User\TokenEncoderInterface;
use App\Core\Validator\FormValidator;
use App\Rest\Controller\Response\Announcement\AnnouncementCollectionResponse;
use App\Rest\Controller\Response\Announcement\AnnouncementPageResponse;
use App\Rest\Controller\Response\CollectionResponse;
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

    /** @var FormValidator */
    private $formValidator;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var RouterInterface */
    private $router;

    /** @var EventDispatcherVisitor */
    private $visitVisitor;

    /** @var TokenEncoderInterface */
    private $tokenEncoder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, AnnouncementDtoManagerInterface $announcementManager,
        FormValidator $formValidator, EventDispatcherInterface $eventDispatcher, RouterInterface $router,
        EventDispatcherVisitor $visitVisitor, TokenEncoderInterface $tokenEncoder)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->announcementManager = $announcementManager;
        $this->formValidator = $formValidator;
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
        $this->visitVisitor = $visitVisitor;
        $this->tokenEncoder = $tokenEncoder;
    }


    /**
     * Lists announcements
     *
     * @Rest\Get(name="rest_get_announcements")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters", default="createdAt")
     *
     * @Operation(tags={ "Announcement" },
     *   @SWG\Response(response=200, description="Announcements found", @Model(type=AnnouncementPageResponse::class)),
     *   @SWG\Response(response=206, description="Partial content")
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

        $this->logger->debug("Listing announcements", $parameters);

        $pageable = PageRequest::create($parameters);
        $response = new PageResponse(
            $this->announcementManager->list($pageable),
            "rest_get_announcements", $paramFetcher->all(),
            $pageable, $this->announcementManager->countAll());

        $this->logger->info("Listing announcements - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Create a new announcement for the authenticated user
     *
     * @Rest\Post(name="rest_create_announcement")
     * @Security(expression="has_role('ROLE_PROPOSAL')")
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
     * @throws InvalidCreatorException
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
        $this->logger->debug("Putting an announcement", array ("id" => $id, "putParams" => $request->request->all()));

        return $this->handleUpdateAnnouncementRequest($id, $request, true);
    }


    /**
     * Updates (partial) an existing announcement
     *
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
    public function patchAnnouncementAction(int $id, Request $request)
    {
        $this->logger->debug("Patching an announcement",
            array ("id" => $id, "patchParams" => $request->request->all()));

        return $this->handleUpdateAnnouncementRequest($id, $request, false);
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
     * Searches specific announcements
     *
     * @Rest\Post(path="/searches", name="rest_search_announcements")
     *
     * @Operation(tags={ "Announcement" },
     *   @SWG\Parameter(name="filter", in="body", required=true, description="Criteria filter",
     *     @Model(type=AnnouncementFilterForm::class)),
     *   @SWG\Response(
     *     response=200, description="Announcements found", @Model(type=AnnouncementCollectionResponse::class)),
     *   @SWG\Response(response=400, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function searchAnnouncementsAction(Request $request)
    {
        $this->logger->debug("Searching specific announcements", array ("postParams" => $request->request->all()));

        /** @var AnnouncementFilter $filter */
        $filter = $this->formValidator->validateFilterForm(AnnouncementFilterForm::class, new AnnouncementFilter(),
            $request->request->all());
        $response = new CollectionResponse(
            $this->announcementManager->search($filter, $filter->getPageable()), "rest_search_announcements", [],
            $this->announcementManager->countBy($filter));

        $this->logger->info("Searching announcements by filter - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response);
    }


    /**
     * Gets all candidates of an existing announcement
     *
     * @Rest\Get(path="/{id}/candidates", name="rest_get_announcement_candidates",
     *   requirements={"id"="\d+"})
     * @Security(expression="has_role('ROLE_USER')")
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
     * @Security("has_role('ROLE_USER')")
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
        $this->evaluateUserAccess(AnnouncementVoter::REMOVE_CANDIDATE, $announcement);

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


    /**
     * Handles the update operation on the announcement
     *
     * @param int $id The announcement identifier
     * @param Request $request The current request
     * @param bool $fullUpdate If the operation is a patch or a full update
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    private function handleUpdateAnnouncementRequest(int $id, Request $request, bool $fullUpdate)
    {
        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        $this->evaluateUserAccess(AnnouncementVoter::UPDATE, $announcement);
        $announcement = $this->announcementManager->update($announcement, $request->request->all(), $fullUpdate);

        $this->logger->info("Announcement updated", array ("response" => $announcement));

        return $this->buildJsonResponse($announcement);
    }

}
