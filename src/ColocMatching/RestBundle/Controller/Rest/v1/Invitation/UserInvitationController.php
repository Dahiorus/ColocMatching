<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Invitation;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\DTO\Invitation\InvitableDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Group\GroupDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Invitation\InvitationDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageRequest;
use ColocMatching\CoreBundle\Security\User\TokenEncoderInterface;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use ColocMatching\RestBundle\Security\Authorization\Voter\InvitationVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * REST controller for resources /users/{id}/invitations
 *
 * @Rest\Route(path="/users/{id}/invitations", requirements={ "id": "\d+" },
 *   service="coloc_matching.rest.user_invitation_controller")
 * @Security(expression="has_role('ROLE_USER')")
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


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, InvitationDtoManagerInterface $invitationManager,
        UserDtoManagerInterface $userManager, GroupDtoManagerInterface $groupManager,
        AnnouncementDtoManagerInterface $announcementManager, TokenEncoderInterface $tokenEncoder)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->invitationManager = $invitationManager;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->announcementManager = $announcementManager;
        $this->tokenEncoder = $tokenEncoder;
    }


    /**
     * Lists the invitations with the user as the recipient with pagination
     *
     * @Rest\Get(name="rest_get_user_invitations")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", map=true, nullable=true, requirements="\w+,(asc|desc)", default="createdAt,desc",
     *   allowBlank=false, description="Sorting parameters")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function getInvitationsAction(int $id, ParamFetcher $paramFetcher)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->info("Listing a user invitations", array_merge(array ("id" => $id), $parameters));

        $pageable = PageRequest::create($parameters);
        /** @var UserDto $user */
        $user = $this->userManager->read($id);

        $this->evaluateUserAccess(InvitationVoter::LIST, $user);

        $response = new PageResponse(
            $this->invitationManager->listByRecipient($user, $pageable),
            "rest_get_user_invitations", array_merge(array ("id" => $id), $parameters),
            $pageable, $this->invitationManager->countByRecipient($user));

        $this->logger->info("Listing a user invitations - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Creates an invitation with the user as the recipient
     *
     * @Rest\Post(name="rest_create_user_invitation")
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
        $this->logger->info("Creating an invitation for a user",
            array ("id" => $id, "postParams" => $request->request->all()));

        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);
        /** @var UserDto $recipient */
        $recipient = $this->userManager->read($id);

        $this->evaluateUserAccess(InvitationVoter::INVITE, $recipient);

        if ($user->getId() == $recipient->getId() || !$this->isCreationPossible($user, $recipient))
        {
            throw new AccessDeniedException();
        }

        /** @var InvitableDto $invitable */
        $invitable = $this->getInvitable($user); // the user must have a group or an announcement
        /** @var Invitation $invitation */
        $invitation = $this->invitationManager->create($invitable, $recipient, Invitation::SOURCE_INVITABLE,
            $request->request->all());

        $this->logger->info("Invitation created", array ("response" => $invitation));

        return $this->buildJsonResponse($invitation, Response::HTTP_CREATED);
    }


    /**
     * @param UserDto $user
     *
     * @return AbstractDto
     * @throws EntityNotFoundException
     */
    private function getInvitable(UserDto $user) : AbstractDto
    {
        // getting the user group
        if ($user->getType() == UserConstants::TYPE_SEARCH)
        {
            return $this->groupManager->read($user->getGroupId());
        }

        // getting the user announcement
        if ($user->getType() == UserConstants::TYPE_PROPOSAL)
        {
            return $this->announcementManager->read($user->getAnnouncementId());
        }

        throw new \RuntimeException("Cannot get the user invitable entity");
    }


    /**
     * Tests if the creator can invite the recipient. It is safe to assume the creator has a group or an announcement.
     *
     * @param UserDto $creator The invitable entity creator
     * @param UserDto $recipient The invitation recipient
     *
     * @return bool
     * @throws ORMException
     */
    private function isCreationPossible(UserDto $creator, UserDto $recipient) : bool
    {
        // cannot invite someone who is already in a group
        if ($creator->getType() == UserConstants::TYPE_SEARCH && empty($recipient->getGroupId())
            && !empty($this->groupManager->findByMember($recipient)))
        {
            $this->logger->warning("The recipient is already in a group");

            return false;
        }

        // cannot invite someone who is already in an announcement
        if ($creator->getType() == UserConstants::TYPE_PROPOSAL
            && !empty($this->announcementManager->findByCandidate($recipient)))
        {
            $this->logger->warning("The recipient is already in an announcement");

            return false;
        }

        return true;
    }

}
