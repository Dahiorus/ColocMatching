<?php

namespace App\Rest\Controller\v1\Invitation;

use App\Core\DTO\Invitation\InvitableDto;
use App\Core\DTO\Invitation\InvitationDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\User\UserType;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidCreatorException;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Form\Type\Invitation\UserInvitationDtoForm;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\Invitation\InvitationDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Security\User\TokenEncoderInterface;
use App\Rest\Controller\Response\Invitation\InvitationPageResponse;
use App\Rest\Controller\Response\PageResponse;
use App\Rest\Controller\v1\AbstractRestController;
use App\Rest\Event\Events;
use App\Rest\Event\InvitationCreatedEvent;
use App\Rest\Security\Authorization\Voter\InvitationVoter;
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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * REST controller for resources /users/{id}/invitations
 *
 * @Rest\Route(path="/users/{id}/invitations", requirements={ "id": "\d+" })
 * @Security(expression="is_granted('ROLE_USER')")
 *
 * @author Dahiorus
 */
class UserInvitationController extends AbstractRestController
{
    /** @var InvitationDtoManagerInterface */
    private $invitationManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var GroupDtoManagerInterface */
    private $groupManager;

    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var TokenEncoderInterface */
    private $tokenEncoder;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, InvitationDtoManagerInterface $invitationManager,
        UserDtoManagerInterface $userManager, GroupDtoManagerInterface $groupManager,
        AnnouncementDtoManagerInterface $announcementManager, TokenEncoderInterface $tokenEncoder,
        EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->invitationManager = $invitationManager;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->announcementManager = $announcementManager;
        $this->tokenEncoder = $tokenEncoder;
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * Lists the invitations with the user as the recipient with pagination
     *
     * @Rest\Get(name="rest_get_user_invitations")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters (prefix with '-' to DESC sort)",
     *   default="-createdAt")
     *
     * @Operation(tags={ "Invitation" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(response=200, description="Invitation found", @Model(type=InvitationPageResponse::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No user found")
     * )
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getInvitationsAction(int $id, ParamFetcher $paramFetcher)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->debug("Listing a user invitations", array_merge(array ("id" => $id), $parameters));

        $pageable = PageRequest::create($parameters);
        /** @var UserDto $user */
        $user = $this->userManager->read($id);

        $this->evaluateUserAccess(InvitationVoter::LIST, $user);

        $response = new PageResponse(
            $this->invitationManager->listByRecipient($user, $pageable),
            "rest_get_user_invitations", array_merge(array ("id" => $id), $parameters));

        $this->logger->info("Listing a user invitations - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Creates an invitation with the user as the recipient
     *
     * @Rest\Post(name="rest_create_user_invitation")
     *
     * @Operation(tags={ "Invitation" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(in="body", name="invitation", required=true, description="The invitation to create",
     *     @Model(type=UserInvitationDtoForm::class)),
     *   @SWG\Response(response=201, description="Invitation created", @Model(type=InvitationDto::class)),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No user found")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidParameterException
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function createInvitationAction(int $id, Request $request)
    {
        $this->logger->debug("Creating an invitation for a user",
            array ("id" => $id, "postParams" => $request->request->all()));

        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);
        /** @var UserDto $recipient */
        $recipient = $this->userManager->read($id);

        $this->evaluateUserAccess(InvitationVoter::INVITE, $recipient);

        if ($user->getId() == $recipient->getId() || !$this->isCreationPossible($user, $recipient))
        {
            throw new AccessDeniedException("Not allowed to invite the user $id");
        }

        $data = $request->request->all();
        /** @var InvitableDto $invitable */
        $invitable = $this->getInvitable($user, $data); // the user must have a group or an announcement
        /** @var InvitationDto $invitation */
        $invitation = $this->invitationManager->create($invitable, $recipient, Invitation::SOURCE_INVITABLE, $data);

        $this->eventDispatcher->dispatch(Events::INVITATION_CREATED_EVENT, new InvitationCreatedEvent($invitation));

        $this->logger->info("Invitation created", array ("response" => $invitation));

        return $this->buildJsonResponse($invitation, Response::HTTP_CREATED);
    }


    /**
     * @param UserDto $user
     * @param array $data
     * @return InvitableDto
     * @throws EntityNotFoundException
     * @throws InvalidCreatorException
     * @throws InvalidParameterException
     */
    private function getInvitable(UserDto $user, array &$data) : InvitableDto
    {
        if (!isset($data["invitableId"]) || !is_int($data["invitableId"]))
        {
            throw new InvalidParameterException("Data parameter 'invitableId' is required and must be an integer");
        }

        $invitableId = $data["invitableId"];

        // getting the user group
        if ($user->getType() == UserType::SEARCH)
        {
            /** @var InvitableDto $invitableDto */
            $invitableDto = $this->groupManager->read($invitableId);
        }
        // getting the user announcement
        else if ($user->getType() == UserType::PROPOSAL)
        {
            /** @var InvitableDto $invitableDto */
            $invitableDto = $this->announcementManager->read($invitableId);
        }
        else
        {
            throw new \RuntimeException("Cannot get the user invitable entity");
        }

        if ($invitableDto->getCreatorId() != $user->getId())
        {
            throw new InvalidCreatorException("The user is not the creator of the invitable $invitableId");
        }

        unset($data["invitableId"]); // this datum should cause validation error

        return $invitableDto;
    }


    /**
     * Tests if the creator can invite the recipient. It is safe to assume the creator has a group or an announcement.
     *
     * @param UserDto $creator The invitable entity creator
     * @param UserDto $recipient The invitation recipient
     *
     * @return bool
     * @throws ORMException
     * @throws EntityNotFoundException
     */
    private function isCreationPossible(UserDto $creator, UserDto $recipient) : bool
    {
        // cannot invite someone who is already in an announcement
        if ($creator->getType() == UserType::PROPOSAL
            && !empty($this->announcementManager->findByCandidate($recipient)))
        {
            $this->logger->warning("The recipient is already in an announcement");

            return false;
        }

        return true;
    }

}
