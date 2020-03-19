<?php

namespace App\Rest\Listener;

use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\HistoricAnnouncement;
use App\Core\Entity\User\User;
use App\Core\Manager\Notification\MailManager;
use App\Core\Mapper\User\UserDtoMapper;
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
    private const DELETION_MAIL_TEMPLATE = "mail/Announcement/announcement_deletion_mail.html.twig";
    private const DELETION_MAIL_SUBJECT = "mail.subject.announcement.deletion";

    /** @var LoggerInterface */
    private $logger;

    /** @var MailManager */
    private $mailManager;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var UserDtoMapper */
    private $userDtoMapper;


    public function __construct(LoggerInterface $logger, MailManager $mailManager,
        EntityManagerInterface $entityManager, UserDtoMapper $userDtoMapper)
    {
        $this->logger = $logger;
        $this->mailManager = $mailManager;
        $this->entityManager = $entityManager;
        $this->userDtoMapper = $userDtoMapper;
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
        $this->logger->debug("Creating a historic entry from the event [{event}]", array ("event" => $event));

        $historicAnnouncement = HistoricAnnouncement::create($this->getAnnouncement($event));
        $this->entityManager->persist($historicAnnouncement);

        $this->logger->info("Historic announcement created [{entry}]", array ("entry" => $historicAnnouncement));
    }


    /**
     * Callback event before the announcement is deleted.
     * Sends an e-mail to all candidates to inform them of the deletion
     *
     * @param DeleteAnnouncementEvent $event The event linked to the announcement deletion
     */
    private function sendMailToCandidates(DeleteAnnouncementEvent $event)
    {
        $this->logger->debug("Sending an e-mail to each announcement candidate from the event [{event}]",
            array ("event" => $event));

        /** @var Announcement $announcement */
        $announcement = $this->getAnnouncement($event);
        $candidates = $announcement->getCandidates();

        /** @var User $candidate */
        foreach ($candidates as $candidate)
        {
            $this->sendMailToCandidate($candidate, $announcement);
        }

        $this->logger->info("{count} mail(s) sent", array ("count" => $candidates->count()));
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
        $recipient = $this->userDtoMapper->toDto($user);
        $creator = $this->userDtoMapper->toDto($announcement->getCreator());

        $this->mailManager->sendEmail(
            $recipient, self::DELETION_MAIL_SUBJECT, self::DELETION_MAIL_TEMPLATE, $subjectParameters,
            array ("title" => $announcement->getTitle(), "recipient" => $recipient, "creator" => $creator));
    }


    private function getAnnouncement(DeleteAnnouncementEvent $event) : Announcement
    {
        return $this->entityManager->getRepository(Announcement::class)->find($event->getAnnouncementId());
    }

}
