<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use Doctrine\ORM\Mapping as ORM;

class InvitationListener extends MailerListener {

    private const INVITATION_MAIL_TEMPLATE = "MailBundle:Invitation:invitation_mail.html.twig";


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

        if ($invitation->getSourceType() == Invitation::SOURCE_INVITABLE) {
            $emailRecipient = $invitationRecipient;
            $subject = $this->translator->trans("text.mail.invitation.invitable.subject",
                array ("%name%" => $invitationRecipient->getDisplayName()));
            $templateParameters = array ("message" => $invitation->getMessage(), "recipient" => $invitationRecipient,
                "from" => $invitableCreator);

            $templateParameters["messageKey"] = ($invitation->getInvitable() instanceof Announcement) ?
                "text.mail.invitation.invitable.message.announcement.html"
                : "text.mail.invitation.invitable.message.group.html";
        }
        else {
            $emailRecipient = $invitableCreator;
            $subject = $this->translator->trans("text.mail.invitation.search.subject",
                array ("%name%" => $invitationRecipient->getDisplayName()));
            $templateParameters = array ("message" => $invitation->getMessage(), "recipient" => $invitableCreator,
                "from" => $invitationRecipient);

            $templateParameters["messageKey"] = ($invitation->getInvitable() instanceof Announcement) ?
                "text.mail.invitation.search.message.announcement.html"
                : "text.mail.invitation.search.message.group.html";
        }

        $templateParameters["link"] = "LINK_TODO"; // TODO manage link

        $this->sendMail($emailRecipient, $subject, $templateParameters);
    }


    protected function getMailTemplate() : string {
        return self::INVITATION_MAIL_TEMPLATE;
    }

}