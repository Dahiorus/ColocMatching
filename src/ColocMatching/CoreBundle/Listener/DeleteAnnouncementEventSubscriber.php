<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\DAO\AnnouncementDao;
use ColocMatching\CoreBundle\DAO\HistoricAnnouncementDao;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Event\DeleteAnnouncementEvent;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Service\MailerService;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for an announcement deletion
 *
 * @author Dahiorus
 */
class DeleteAnnouncementEventSubscriber implements EventSubscriberInterface
{
    const DELETION_MAIL_TEMPLATE = "MailBundle:Announcement:deletion_mail.html.twig";

    /** @var LoggerInterface */
    protected $logger;

    /** @var MailerService */
    private $mailer;

    /** @var AnnouncementDao */
    private $announcementDao;

    /** @var HistoricAnnouncementDao */
    private $historicAnnouncementDao;


    public function __construct(LoggerInterface $logger, MailerService $mailer, AnnouncementDao $announcementDao,
        HistoricAnnouncementDao $historicAnnouncementDao)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->announcementDao = $announcementDao;
        $this->historicAnnouncementDao = $historicAnnouncementDao;
    }


    public static function getSubscribedEvents()
    {
        return array (DeleteAnnouncementEvent::DELETE_EVENT => "onDeleteEvent");
    }


    /**
     * Callback event before the announcement is deleted
     *
     * @param DeleteAnnouncementEvent $event The event linked to the announcement deletion
     *
     * @throws EntityNotFoundException
     * @throws ORMException
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
     *
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    private function createHistoricEntry(DeleteAnnouncementEvent $event)
    {
        $this->logger->debug("Creating a historic entry of an announcement",
            array ("announcementId" => $event->getAnnouncementId()));

        /** @var Announcement $announcement */
        $announcement = $this->announcementDao->get($event->getAnnouncementId());
        $historicAnnouncement = HistoricAnnouncement::create($announcement);
        $this->historicAnnouncementDao->persist($historicAnnouncement);

        $this->logger->debug("Historic announcement created", array ("historicEntry" => $historicAnnouncement));
    }


    /**
     * Callback event before the announcement is deleted.
     * Sends an e-mail to all candidates to inform them of the deletion
     *
     * @param DeleteAnnouncementEvent $event The event linked to the announcement deletion
     *
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    private function sendMailToCandidates(DeleteAnnouncementEvent $event)
    {
        $this->logger->debug("Sending an e-mail to all candidates of an announcement",
            array ("announcementId" => $event->getAnnouncementId()));

        /** @var Announcement $announcement */
        $announcement = $this->announcementDao->get($event->getAnnouncementId());
        $candidates = $announcement->getCandidates();

        /** @var User $candidate */
        foreach ($candidates as $candidate)
        {
            $this->sendMailToCandidate($candidate, $announcement);
        }

        $this->logger->debug(sprintf("%d mail(s) sent", $candidates->count()));
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

        $subject = "text.mail.announcement.deletion.subject";
        $subjectParameters = array ("%title%" => $announcement->getTitle());

        $this->mailer->sendMail(
            $user, $subject, self::DELETION_MAIL_TEMPLATE, $subjectParameters, array ("announcement" => $announcement));
    }

}
