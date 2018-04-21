<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Event\DeleteAnnouncementEvent;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidCreatorException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Filter\AnnouncementFilterType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageRequest;
use ColocMatching\CoreBundle\Security\User\TokenEncoderInterface;
use ColocMatching\CoreBundle\Service\VisitorInterface;
use ColocMatching\CoreBundle\Validator\FormValidator;
use ColocMatching\RestBundle\Controller\Response\CollectionResponse;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use ColocMatching\RestBundle\Security\Authorization\Voter\AnnouncementVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
 * @Rest\Route(path="/announcements", service="coloc_matching.rest.announcement_controller")
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

    /** @var VisitorInterface */
    private $visitVisitor;

    /** @var TokenEncoderInterface */
    private $tokenEncoder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, AnnouncementDtoManagerInterface $announcementManager,
        FormValidator $formValidator, EventDispatcherInterface $eventDispatcher, RouterInterface $router,
        VisitorInterface $visitVisitor, TokenEncoderInterface $tokenEncoder)
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
     * Lists announcements or fields with pagination
     *
     * @Rest\Get(name="rest_get_announcements")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", map=true, nullable=true, requirements="\w+,(asc|desc)", default="createdAt,asc",
     *   allowBlank=false, description="Sorting parameters")
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getAnnouncementsAction(ParamFetcher $paramFetcher)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->info("Listing announcements", $parameters);

        $pageable = PageRequest::create($parameters);
        $response = new PageResponse(
            $this->announcementManager->list($pageable),
            "rest_get_announcements", $paramFetcher->all(),
            $pageable, $this->announcementManager->countAll());

        $this->logger->info("Listing announcements - result information",
            array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Create a new announcement for the authenticated user
     *
     * @Rest\Post(name="rest_create_announcement")
     * @Security(expression="has_role('ROLE_PROPOSAL')")
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

        $this->logger->info("Posting a new announcement",
            array ("user" => $user, "postParams" => $request->request->all()));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->create($user, $request->request->all());

        $this->logger->info("Announcement created", array ("response" => $announcement));

        return $this->buildJsonResponse($announcement,
            Response::HTTP_CREATED, array ("Location" => $this->router->generate("rest_get_announcement",
                array ("id" => $announcement->getId()), Router::ABSOLUTE_URL)));
    }


    /**
     * Gets an existing announcement or its fields
     *
     * @Rest\Get(path="/{id}", name="rest_get_announcement", requirements={"id"="\d+"})
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function getAnnouncementAction(int $id)
    {
        $this->logger->info("Getting an existing announcement", array ("id" => $id));

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
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function updateAnnouncementAction(int $id, Request $request)
    {
        $this->logger->info("Putting an announcement", array ("id" => $id, "putParams" => $request->request->all()));

        return $this->handleUpdateAnnouncementRequest($id, $request, true);
    }


    /**
     * Updates (partial) an existing announcement
     *
     * @Rest\Patch(path="/{id}", name="rest_patch_announcement", requirements={"id"="\d+"})
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
        $this->logger->info("Patching an announcement", array ("id" => $id, "patchParams" => $request->request->all()));

        return $this->handleUpdateAnnouncementRequest($id, $request, false);
    }


    /**
     * Deletes an existing announcement
     *
     * @Rest\Delete(path="/{id}", name="rest_delete_announcement", requirements={"id"="\d+"})
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
            $this->eventDispatcher->dispatch(DeleteAnnouncementEvent::DELETE_EVENT,
                new DeleteAnnouncementEvent($announcement->getId()));
            $this->announcementManager->delete($announcement);
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->warning("Trying to delete an non existing announcement", array ("id" => $id));
        }

        return new JsonResponse("Announcement deleted");
    }


    /**
     * Searches announcements by criteria
     *
     * @Rest\Post(path="/searches", name="rest_search_announcements")
     * @Rest\RequestParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\RequestParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\RequestParam(name="sorts", map=true, nullable=true, requirements="\w+,(asc|desc)", default="createdAt,asc",
     *   allowBlank=false, description="Sorting parameters")
     *
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function searchAnnouncementsAction(ParamFetcher $paramFetcher, Request $request)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->info("Searching specific announcements",
            array_merge(array ("postParams" => $request->request->all()), $parameters));

        $filter = $this->formValidator->validateFilterForm(AnnouncementFilterType::class, new AnnouncementFilter(),
            $request->request->all());
        $pageable = PageRequest::create($parameters);
        $response = new CollectionResponse($this->announcementManager->search($filter, $pageable),
            "rest_search_announcements");

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
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getCandidatesAction(int $id)
    {
        $this->logger->info("Getting all candidates of an existing announcement", array ("id" => $id));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        /** @var UserDto[] $candidates */
        $candidates = $this->announcementManager->getCandidates($announcement);

        return $this->buildJsonResponse($candidates);
    }


    /**
     * Removes a candidate from an existing announcement
     *
     * @Rest\Delete(path="/{id}/candidates/{userId}", name="rest_remove_announcement_candidate",
     *   requirements={"id"="\d+"})
     * @Security("has_role('ROLE_USER')")
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
        $this->logger->info("Removing a candidate from an existing announcement",
            array ("id" => $id, "userId" => $userId));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        $this->evaluateUserAccess(AnnouncementVoter::REMOVE_CANDIDATE, $announcement);

        $candidate = new UserDto();
        $candidate->setId($userId);

        $this->announcementManager->removeCandidate($announcement, $candidate);

        return new JsonResponse("Candidate removed");
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
