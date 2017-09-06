<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementManagerInterface;
use ColocMatching\MailBundle\Service\HtmlMailSender;
use ColocMatching\MailBundle\Service\MailSenderInterface;
use Doctrine\ORM\Mapping\PreRemove;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Event listener for announcement.
 *
 * @author Dahiorus
 */
class AnnouncementListener {

    const DELETION_MAIL_TEMPLATE = "MailBundle:Announcement:deletion_mail.html.twig";

    /**
     * @var HistoricAnnouncementManagerInterface
     */
    private $historicAnnouncementManager;

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


    public function __construct(HistoricAnnouncementManagerInterface $historicAnnouncementManager,
        MailSenderInterface $mailSender, TranslatorInterface $translator, LoggerInterface $logger) {
        $this->historicAnnouncementManager = $historicAnnouncementManager;
        $this->mailSender = $mailSender;
        $this->translator = $translator;
        $this->logger = $logger;
    }


    /**
     * Callback event before the announcement is deleted.
     * Creates an historic announcement to save the announcement in history.
     *
     * @PreRemove()
     *
     * @param Announcement $announcement The deleted announcement to save in history
     */
    public function createHistoricEntry(Announcement $announcement) {
        $this->logger->info("Creating a historic entry of an announcement", array ("announcement" => $announcement));

        $historicAnnouncement = $this->historicAnnouncementManager->create($announcement);

        $this->logger->info("HistoricAnnouncement announcement created",
            array ("historicAnnouncement" => $historicAnnouncement));
    }


    /**
     * Callback event before the announcement is deleted.
     * Sends an e-mail to all candidates to inform them of the deletion
     *
     * @PreRemove()
     *
     * @param Announcement $announcement The deleted announcement
     */
    public function sendMailToCandidates(Announcement $announcement) {
        $this->logger->info("Sending an e-mail to all candidates of an announcement",
            array ("announcement" => $announcement));

        $candidates = $announcement->getCandidates();

        foreach ($candidates as $candidate) {
            $this->sendMail($candidate, $announcement);
        }

        $this->logger->debug(sprintf("%d mails sent", $candidates->count()));
    }


    private function sendMail(User $user, Announcement $announcement) {
        $this->logger->debug("Sending an e-mail to a user", array ("user" => $user));

        $subject = $this->translator->trans("text.mail.announcement.deletion.subject",
            array ("%title%" => $announcement->getTitle()));

        $this->mailSender->sendHtmlMail("no-reply@coloc-matching.fr", $user->getEmail(), $subject,
            self::DELETION_MAIL_TEMPLATE, array ("announcement" => $announcement, "candidate" => $user));
    }

}