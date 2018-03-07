<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\MailBundle\Service\MailSenderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\PreRemove;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Event listener for announcement.
 *
 * @author Dahiorus
 */
// TODO transform to event subscriber
class AnnouncementListener extends MailerListener
{
    const DELETION_MAIL_TEMPLATE = "MailBundle:Announcement:deletion_mail.html.twig";

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager,
        MailSenderInterface $mailSender, TranslatorInterface $translator, string $from, LoggerInterface $logger)
    {
        parent::__construct($mailSender, $translator, $from, $logger);
        $this->entityManager = $entityManager;
    }


    /**
     * Callback event before the announcement is deleted.
     * Creates an historic announcement to save the announcement in history.
     *
     * @PreRemove
     *
     * @param Announcement $announcement The deleted announcement to save in history
     */
    public function createHistoricEntry(Announcement $announcement)
    {
        $this->logger->debug("Creating a historic entry of an announcement", array ("announcement" => $announcement));

        $historicAnnouncement = HistoricAnnouncement::create($announcement);
        $this->entityManager->persist($historicAnnouncement);

        $this->logger->debug("HistoricAnnouncement announcement created",
            array ("historicAnnouncement" => $historicAnnouncement));
    }


    /**
     * Callback event before the announcement is deleted.
     * Sends an e-mail to all candidates to inform them of the deletion
     *
     * @PreRemove
     *
     * @param Announcement $announcement The deleted announcement
     */
    public function sendMailToCandidates(Announcement $announcement)
    {
        $this->logger->info("Sending an e-mail to all candidates of an announcement",
            array ("announcement" => $announcement));

        $candidates = $announcement->getCandidates();

        foreach ($candidates as $candidate)
        {
            $this->sendMailToCandidate($candidate, $announcement);
        }

        $this->logger->debug(sprintf("%d mails sent", $candidates->count()));
    }


    /**
     * Sends an e-mail informing of the deletion of an announcement to a candidate
     *
     * @param User $user The candidate of the announcement
     * @param Announcement $announcement The announcement to be deleted
     */
    private function sendMailToCandidate(User $user, Announcement $announcement)
    {
        $this->logger->debug("Sending an e-mail to a user", array ("user" => $user));

        $subject = $this->translator->trans("text.mail.announcement.deletion.subject",
            array ("%title%" => $announcement->getTitle()));

        $this->sendMail($user, $subject, array ("announcement" => $announcement, "candidate" => $user));
    }


    protected function getMailTemplate() : string
    {
        return self::DELETION_MAIL_TEMPLATE;
    }

}