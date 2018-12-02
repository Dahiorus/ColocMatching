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
    private const ANSWER_MAIL_TEMPLATE = "mail/Invitation/invitation_answer_%s_%s_mail.html.twig";
    private const ANSWER_MAIL_SUBJECT = "mail.subject.invitation.answer";

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
     * Send an invitation email for the specified invitation
     *
     * @param InvitationDto $invitation The invitation
     *
     * @throws EntityNotFoundException
     */
    public function sendInvitationMail(InvitationDto $invitation) : void
    {
        $this->logger->debug("Sending an invitation email from [{invitation}]", array ("invitation" => $invitation));

        $invitationRecipient = $this->userManager->read($invitation->getRecipientId());
        $invitableCreator = $this->getInvitableCreator($invitation);
        $sourceType = $invitation->getSourceType();

        if ($sourceType == Invitation::SOURCE_INVITABLE)
        {
            $from = $invitableCreator;
            $to = $invitationRecipient;
        }
        else if ($sourceType == Invitation::SOURCE_SEARCH)
        {
            $from = $invitationRecipient;
            $to = $invitableCreator;
        }
        else
        {
            throw new \RuntimeException("Error while getting the mail recipient for the invitation [$invitation]");
        }

        $template = sprintf(self::INVITATION_MAIL_TEMPLATE, $sourceType, $this->getInvitableType($invitation));
        $subject = self::INVITATION_MAIL_SUBJECT_PREFIX . $sourceType;

        $subjectParams = array ("%name%" => $from->getDisplayName());
        $bodyParams = array (
            "message" => $invitation->getMessage(),
            "from" => $from,
            "recipient" => $to,
            "id" => $invitation->getInvitableId()
        );

        $this->mailManager->sendEmail($to, $subject, $template, $subjectParams, $bodyParams);
    }


    /**
     * Send an answer email for the specified invitation
     *
     * @param InvitationDto $invitation The invitation
     *
     * @throws EntityNotFoundException
     */
    public function sendAnswerMail(InvitationDto $invitation) : void
    {
        $this->logger->debug("Sending an answer email from [{invitation}]", array ("invitation" => $invitation));

        $invitationRecipient = $this->userManager->read($invitation->getRecipientId());
        $invitableCreator = $this->getInvitableCreator($invitation);
        $sourceType = $invitation->getSourceType();

        if ($sourceType == Invitation::SOURCE_INVITABLE)
        {
            $from = $invitationRecipient;
            $to = $invitableCreator;
        }
        else if ($sourceType == Invitation::SOURCE_SEARCH)
        {
            $from = $invitableCreator;
            $to = $invitationRecipient;
        }
        else
        {
            throw new \RuntimeException("Error while getting the answer mail recipient for the invitation [$invitation]");
        }

        $template = sprintf(self::ANSWER_MAIL_TEMPLATE, $sourceType, $this->getInvitableType($invitation));
        $subjectParams = array ("%name%" => $from->getDisplayName());
        $bodyParams = array (
            "from" => $from,
            "recipient" => $to,
            "id" => $invitation->getInvitableId()
        );

        $this->mailManager->sendEmail($to, self::ANSWER_MAIL_SUBJECT, $template, $subjectParams, $bodyParams);
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


    /**
     * Get the invitable type of the invitation
     *
     * @param InvitationDto $invitation The invitation
     *
     * @return string The simple name of the invitation invitable class
     */
    private function getInvitableType(InvitationDto $invitation) : string
    {
        try
        {
            $class = new \ReflectionClass($invitation->getInvitableClass());

            return strtolower($class->getShortName());
        }
        catch (\ReflectionException $e)
        {
            throw new \RuntimeException("Unable to get the invitation [$invitation] invitable type");
        }
    }

}