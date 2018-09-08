<?php

namespace App\Rest\Listener;

use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\HistoricAnnouncement;
use App\Core\Entity\User\User;
use App\Core\Service\MailerService;
use App\Rest\Event\DeleteAnnouncementEvent;
use App\Rest\Event\Events;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for an announcement deletion
 *
 * @author Dahiorus
 */
class DeleteAnnouncementEventSubscriber implements EventSubscriberInterface
{
    private const DELETION_MAIL_TEMPLATE = "mail/Announcement/deletion_mail.html.twig";
    private const DELETION_MAIL_SUBJECT = "mail.subject.announcement.deletion";

    /** @var LoggerInterface */
    private $logger;

    /** @var MailerService */
    private $mailer;

    /** @var EntityManagerInterface */
    private $entityManager;


    public function __construct(LoggerInterface $logger, MailerService $mailer, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
    }


    public static function getSubscribedEvents()
    {
        return array (Events::DELETE_ANNOUNCEMENT_EVENT => "onDeleteEvent");
    }


    /**
     * Callback event before the announcement is deleted
     *
     * @param DeleteAnnouncementEvent $event The event linked to the announcement deletion
     */
    public function onDeleteEvent(DeleteAnnouncementEvent $event) : void
    {
        $this->createHistoricEntry($event);
        $this->sendMailToCandidates($event);
    }


    /**
     * Creates an historic announcement to save the announcement in history
     *
     * @param DeleteAnnouncementEvent $event The event linked to the announcement deletion
     */
    private function createHistoricEntry(DeleteAnnouncementEvent $event)
    {
        $this->logger->debug("Creating a historic entry of an announcement",
            array ("announcementId" => $event->getAnnouncementId()));

        $historicAnnouncement = HistoricAnnouncement::create($this->getAnnouncement($event));
        $this->entityManager->persist($historicAnnouncement);

        $this->logger->info("Historic announcement created", array ("historicEntry" => $historicAnnouncement));
    }


    /**
     * Callback event before the announcement is deleted.
     * Sends an e-mail to all candidates to inform them of the deletion
     *
     * @param DeleteAnnouncementEvent $event The event linked to the announcement deletion
     */
    private function sendMailToCandidates(DeleteAnnouncementEvent $event)
    {
        $this->logger->debug("Sending an e-mail to all candidates of an announcement",
            array ("announcementId" => $event->getAnnouncementId()));

        /** @var Announcement $announcement */
        $announcement = $this->getAnnouncement($event);
        $candidates = $announcement->getCandidates();

        /** @var User $candidate */
        foreach ($candidates as $candidate)
        {
            $this->sendMailToCandidate($candidate, $announcement);
        }

        $this->logger->info(sprintf("%d mail(s) sent", $candidates->count()));
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

        $subjectParameters = array ("%title%" => $announcement->getTitle());

        $this->mailer->sendEmail(
            $user, self::DELETION_MAIL_SUBJECT, self::DELETION_MAIL_TEMPLATE, $subjectParameters,
            array ("announcement" => $announcement));
    }


    private function getAnnouncement(DeleteAnnouncementEvent $event) : Announcement
    {
        return $this->entityManager->getRepository(Announcement::class)->find($event->getAnnouncementId());
    }

}
