<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\DAO\AnnouncementDao;
use ColocMatching\CoreBundle\DAO\GroupDao;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\MailBundle\Service\MailSenderInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class InvitationListener extends MailerListener
{
    private const INVITATION_MAIL_TEMPLATE = "MailBundle:Invitation:invitation_mail.html.twig";

    /** @var GroupDao */
    private $groupDao;

    /** @var AnnouncementDao */
    private $announcementDao;

    /** @var UserDtoMapper */
    private $userDtoMapper;


    public function __construct(LoggerInterface $logger, MailSenderInterface $mailSender,
        TranslatorInterface $translator, string $from, AnnouncementDao $announcementDao, GroupDao $groupDao,
        UserDtoMapper $userDtoMapper)
    {
        parent::__construct($mailSender, $translator, $from, $logger);

        $this->announcementDao = $announcementDao;
        $this->groupDao = $groupDao;
        $this->userDtoMapper = $userDtoMapper;
    }


    /**
     * Sends an email to the target of the invitation
     *
     * @ORM\PostPersist
     *
     * @param Invitation $invitation The invitation from witch send the email
     */
    public function sendInvitationMail(Invitation $invitation)
    {
        $invitable = $this->getInvitable($invitation);
        $invitableCreator = $invitable->getCreator();
        $invitationRecipient = $invitation->getRecipient();

        $this->logger->info("Sending invitation email to a user",
            array ("recipient" => $invitationRecipient, "invitableCreator" => $invitableCreator));

        if ($invitation->getSourceType() == Invitation::SOURCE_INVITABLE)
        {
            $emailRecipient = $invitationRecipient;
            $subject = $this->translator->trans("text.mail.invitation.invitable.subject",
                array (
                    "%firstName%" => $invitationRecipient->getFirstname(),
                    "%lastName%" => $invitationRecipient->getLastname())
            );
            $templateParameters = array ("message" => $invitation->getMessage(), "recipient" => $invitationRecipient,
                "from" => $invitableCreator);

            $templateParameters["messageKey"] = ($invitable instanceof Announcement) ?
                "text.mail.invitation.invitable.message.announcement.html"
                : "text.mail.invitation.invitable.message.group.html";
        }
        else
        {
            $emailRecipient = $invitableCreator;
            $subject = $this->translator->trans("text.mail.invitation.search.subject",
                array (
                    "%firstName%" => $invitationRecipient->getFirstname(),
                    "%lastName%" => $invitationRecipient->getLastname())
            );
            $templateParameters = array ("message" => $invitation->getMessage(), "recipient" => $invitableCreator,
                "from" => $invitationRecipient);

            $templateParameters["messageKey"] = ($invitable instanceof Announcement) ?
                "text.mail.invitation.search.message.announcement.html"
                : "text.mail.invitation.search.message.group.html";
        }

        $templateParameters["link"] = "LINK_TODO"; // TODO manage link

        $this->sendMail($this->userDtoMapper->toDto($emailRecipient), $subject, $templateParameters);
    }


    protected function getMailTemplate() : string
    {
        return self::INVITATION_MAIL_TEMPLATE;
    }


    /**
     * Gets the invitation related invitable entity
     *
     * @param Invitation $invitation The invitation
     *
     * @return Invitable
     */
    private function getInvitable(Invitation $invitation) : Invitable
    {
        try
        {
            $invitableClass = $invitation->getInvitableClass();
            $invitableId = $invitation->getInvitableId();

            switch ($invitableClass)
            {
                case Announcement::class:
                    /** @var Announcement $invitable */
                    $invitable = $this->announcementDao->get($invitableId);
                    break;
                case Group::class:
                    /** @var Group $invitable */
                    $invitable = $this->groupDao->get($invitableId);
                    break;
                default:
                    throw new \RuntimeException("Unknown invitable class [$invitableClass]");
            }

            return $invitable;
        }
        catch (EntityNotFoundException | ORMException $e)
        {
            $this->logger->error("Unexpected error while trying to get an invitation invitable entity",
                array ("invitation" => $invitation, "exception" => $e));

            throw new \RuntimeException("Unexpected error", 0, $e);
        }
    }

}