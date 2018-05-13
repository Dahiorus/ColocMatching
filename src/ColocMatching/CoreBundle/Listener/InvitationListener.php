<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InvitationListener
{
    private const INVITATION_MAIL_TEMPLATE = "MailBundle:Invitation:invitation_mail.html.twig";

    /** @var LoggerInterface */
    private $logger;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var MailerService */
    private $mailer;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, MailerService $mailer,
        UrlGeneratorInterface $urlGenerator)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
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
            $templateParameters["link"] = $this->createLink(Invitation::SOURCE_INVITABLE, $invitable);
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
            $templateParameters["link"] = $this->createLink(Invitation::SOURCE_SEARCH, $invitationRecipient);
        }

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
        $invitableClass = $invitation->getInvitableClass();
        $invitableId = $invitation->getInvitableId();

        $repository = $this->entityManager->getRepository($invitableClass);
        /** @var Invitable $invitable */
        $invitable = $repository->find($invitableId);

        return $invitable;
    }


    /**
     * Creates a client app link to the invitation target depending on the invitation source
     *
     * @param string $sourceType The invitation source type
     * @param User|Invitable $subject The invitation link target
     *
     * @return string
     */
    private function createLink(string $sourceType, $subject) : string
    {
        if ($sourceType == Invitation::SOURCE_SEARCH)
        {
            return $this->urlGenerator->generate(
                "coloc_matching.user_url", array ("id" => $subject->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($subject instanceof Group)
        {
            return $this->urlGenerator->generate(
                "coloc_matching.group_url", array ("id" => $subject->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $this->urlGenerator->generate(
            "coloc_matching.announcement_url", array ("id" => $subject->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
    }

}