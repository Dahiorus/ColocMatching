<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\DAO\AnnouncementDao;
use ColocMatching\CoreBundle\DAO\GroupDao;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Service\MailerService;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InvitationListener
{
    private const INVITATION_MAIL_TEMPLATE = "MailBundle:Invitation:invitation_mail.html.twig";

    /** @var LoggerInterface */
    protected $logger;

    /** @var GroupDao */
    private $groupDao;

    /** @var AnnouncementDao */
    private $announcementDao;

    /** @var MailerService */
    private $mailer;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;


    public function __construct(LoggerInterface $logger, GroupDao $groupDao, AnnouncementDao $announcementDao,
        MailerService $mailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->logger = $logger;
        $this->announcementDao = $announcementDao;
        $this->groupDao = $groupDao;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
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

        if ($invitation->getSourceType() == Invitation::SOURCE_INVITABLE)
        {
            $this->logger->debug("Sending invitation email to the invitation recipient",
                array ("recipient" => $invitationRecipient));

            $emailRecipient = $invitationRecipient;

            $subject = "text.mail.invitation.invitable.subject";
            $subjectParameters = array (
                "%firstName%" => $invitationRecipient->getFirstName(),
                "%lastName%" => $invitationRecipient->getLastName());
            $templateParameters = array ("message" => $invitation->getMessage(), "recipient" => $invitationRecipient,
                "from" => $invitableCreator);
            $templateParameters["messageKey"] = ($invitable instanceof Announcement) ?
                "text.mail.invitation.invitable.message.announcement.html"
                : "text.mail.invitation.invitable.message.group.html";
        }
        else
        {
            $this->logger->debug("Sending invitation email to the invitation invitable creator",
                array ("recipient" => $invitableCreator));

            $emailRecipient = $invitableCreator;

            $subject = "text.mail.invitation.search.subject";
            $subjectParameters = array (
                "%firstName%" => $invitationRecipient->getFirstName(),
                "%lastName%" => $invitationRecipient->getLastName());

            $templateParameters = array ("message" => $invitation->getMessage(), "recipient" => $invitableCreator,
                "from" => $invitationRecipient);
            $templateParameters["messageKey"] = ($invitable instanceof Announcement) ?
                "text.mail.invitation.search.message.announcement.html"
                : "text.mail.invitation.search.message.group.html";
        }

        $templateParameters["link"] = "LINK_TODO"; // TODO manage link

        $this->mailer->sendMail(
            $emailRecipient, $subject, self::INVITATION_MAIL_TEMPLATE, $subjectParameters, $templateParameters);

        $this->logger->debug("Invitation mail sent", array ("recipient" => $emailRecipient));
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
                    $invitable = $this->announcementDao->read($invitableId);
                    break;
                case Group::class:
                    /** @var Group $invitable */
                    $invitable = $this->groupDao->read($invitableId);
                    break;
                default:
                    throw new \RuntimeException("Unknown invitable class [$invitableClass]");
            }

            return $invitable;
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->error("Unexpected error while trying to get an invitation invitable entity",
                array ("invitation" => $invitation, "exception" => $e));

            throw new \RuntimeException("Unexpected error", 0, $e);
        }
    }

}