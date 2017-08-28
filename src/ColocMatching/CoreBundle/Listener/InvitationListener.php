<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\MailBundle\Service\HtmlMailSender;
use ColocMatching\MailBundle\Service\MailSenderInterface;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class InvitationListener {

    private const INVITATION_MAIL_TEMPLATE = "MailBundle:Invitation:invitation_mail.html.twig";

    /**
     * @var HtmlMailSender
     */
    private $mailSender;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(MailSenderInterface $mailSender, TranslatorInterface $translator,
        LoggerInterface $logger) {
        $this->mailSender = $mailSender;
        $this->translator = $translator;
        $this->logger = $logger;
    }


    /**
     * Sends an email to the target of the invitation
     *
     * @ORM\PostPersist()
     *
     * @param Invitation $invitation The invitation from witch send the email
     */
    public function sendInvitationMail(Invitation $invitation) {
        $invitableCreator = $invitation->getInvitable()->getCreator();
        $invitationRecipient = $invitation->getRecipient();

        $this->logger->info("Sending invitation email to a user",
            array ("recipient" => $invitationRecipient, "invitableCreator" => $invitableCreator));

        $from = "no-reply@coloc-matching.fr";

        if ($invitation->getSourceType() == Invitation::SOURCE_INVITABLE) {
            $recipientEmail = $invitationRecipient->getEmail();
            $subject = $this->translator->trans("text.mail.invitation.invitable.subject",
                array ("%name%" => $invitationRecipient->getDisplayName()));
            $templateParameters = array ("message" => $invitation->getMessage(), "recipient" => $invitationRecipient,
                "from" => $invitableCreator);

            $templateParameters["messageKey"] = ($invitation->getInvitable() instanceof Announcement) ?
                "text.mail.invitation.invitable.message.announcement.html"
                : "text.mail.invitation.invitable.message.group.html";
        }
        else {
            $recipientEmail = $invitableCreator->getEmail();
            $subject = $this->translator->trans("text.mail.invitation.search.subject",
                array ("%name%" => $invitationRecipient->getDisplayName()));
            $templateParameters = array ("message" => $invitation->getMessage(), "recipient" => $invitableCreator,
                "from" => $invitationRecipient);

            $templateParameters["messageKey"] = ($invitation->getInvitable() instanceof Announcement) ?
                "text.mail.invitation.search.message.announcement.html"
                : "text.mail.invitation.search.message.group.html";
        }

        $templateParameters["link"] = "LINK_TODO"; // TODO manage link

        return $this->mailSender->sendHtmlMail($from, $recipientEmail, $subject,
            self::INVITATION_MAIL_TEMPLATE, $templateParameters);
    }
}