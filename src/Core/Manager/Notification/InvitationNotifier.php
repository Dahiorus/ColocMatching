<?php

namespace App\Core\Manager\Notification;

use App\Core\DTO\Invitation\InvitableDto;
use App\Core\DTO\Invitation\InvitationDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Group\Group;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Notifier class for invitations
 *
 * @author Dahiorus
 */
class InvitationNotifier
{
    private const INVITATION_MAIL_TEMPLATE = "mail/Invitation/invitation_%s_%s_mail.html.twig";
    private const INVITATION_MAIL_SUBJECT_PREFIX = "mail.subject.invitation.";

    /** @var LoggerInterface */
    private $logger;

    /** @var MailManager */
    private $mailManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var GroupDtoManagerInterface */
    private $groupManager;


    public function __construct(LoggerInterface $logger, MailManager $mailManager, UserDtoManagerInterface $userManager,
        AnnouncementDtoManagerInterface $announcementManager, GroupDtoManagerInterface $groupManager)
    {
        $this->logger = $logger;
        $this->mailManager = $mailManager;
        $this->userManager = $userManager;
        $this->announcementManager = $announcementManager;
        $this->groupManager = $groupManager;
    }


    /**
     * Send an email for the specified invitation
     *
     * @param InvitationDto $invitation The invitation
     *
     * @throws EntityNotFoundException
     */
    public function sendInvitationMail(InvitationDto $invitation) : void
    {
        /** @var UserDto $invitationRecipient */
        $invitationRecipient = $this->userManager->read($invitation->getRecipientId());
        $invitableCreator = $this->getInvitableCreator($invitation);

        $sourceType = $invitation->getSourceType();

        if ($sourceType == Invitation::SOURCE_INVITABLE)
        {
            $this->buildMail($invitableCreator, $invitationRecipient, $invitation->getInvitableClass(),
                $invitation->getInvitableId(), $sourceType, $invitation->getMessage());
        }
        else if ($sourceType == Invitation::SOURCE_SEARCH)
        {
            $this->buildMail($invitationRecipient, $invitableCreator, $invitation->getInvitableClass(),
                $invitation->getInvitableId(), $sourceType, $invitation->getMessage());
        }
        else
        {
            throw new \RuntimeException("Unknown invitation [$invitation] source type [$sourceType]");
        }
    }


    /**
     * Build the invitation email from the parameters
     *
     * @param UserDto $from The email sender
     * @param UserDto $to The email recipient
     * @param string $invitableClass The invitation invitable class
     * @param int $invitableId The invitation invitable ID
     * @param string $sourceType The invitation source type
     * @param string $message The invitation message
     */
    private function buildMail(UserDto $from, UserDto $to, string $invitableClass, int $invitableId, string $sourceType,
        string $message) : void
    {
        $this->logger->debug("Sending an invitation email from [{from}] to [{to}]",
            array ("from" => $from, "to" => $to));

        if ($invitableClass == Announcement::class)
        {
            $invitableType = "announcement";
        }
        else if ($invitableClass == Group::class)
        {
            $invitableType = "group";
        }
        else
        {
            throw new \InvalidArgumentException("Unknown invitable type given by [$invitableClass]");
        }

        $template = sprintf(self::INVITATION_MAIL_TEMPLATE, $sourceType, $invitableType);
        $subject = self::INVITATION_MAIL_SUBJECT_PREFIX . $sourceType;

        $subjectParams = array ("%name%" => $from->getDisplayName());
        $bodyParams = array (
            "message" => $message,
            "from" => $from,
            "recipient" => $to,
            "id" => $invitableId
        );

        $this->mailManager->sendEmail($to, $subject, $template, $subjectParams, $bodyParams);
    }


    /**
     * Gets the invitation invitable creator
     *
     * @param InvitationDto $invitation
     *
     * @return UserDto
     * @throws EntityNotFoundException
     */
    private function getInvitableCreator(InvitationDto $invitation) : UserDto
    {
        if ($invitation->getInvitableClass() == Announcement::class)
        {
            /** @var InvitableDto $invitable */
            $invitable = $this->announcementManager->read($invitation->getInvitableId());
        }
        else if ($invitation->getInvitableClass() == Group::class)
        {
            /** @var InvitableDto $invitable */
            $invitable = $this->groupManager->read($invitation->getInvitableId());
        }
        else
        {
            throw new \RuntimeException("Cannot get the invitable from the invitation [$invitation]");
        }

        /** @var UserDto $creator */
        $creator = $this->userManager->read($invitable->getCreatorId());

        return $creator;
    }

}