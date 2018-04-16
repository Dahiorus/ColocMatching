<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Event\DeleteAnnouncementEvent;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\MailBundle\Service\MailSenderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Event subscriber for an announcement deletion
 *
 * @author Dahiorus
 */
class DeleteAnnouncementEventSubscriber extends MailerListener implements EventSubscriberInterface
{
    const DELETION_MAIL_TEMPLATE = "MailBundle:Announcement:deletion_mail.html.twig";

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /** @var UserDtoMapper */
    private $userDtoMapper;


    public function __construct(LoggerInterface $logger, MailSenderInterface $mailSender,
        TranslatorInterface $translator, string $from, EntityManagerInterface $entityManager,
        UserDtoMapper $userDtoMapper)
    {
        parent::__construct($mailSender, $translator, $from, $logger);

        $this->entityManager = $entityManager;
        $this->userDtoMapper = $userDtoMapper;
    }


    public static function getSubscribedEvents()
    {
        return array (DeleteAnnouncementEvent::DELETE_EVENT => "onDeleteEvent");
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

        /** @var Announcement $announcement */
        $announcement = $this->entityManager->find(Announcement::class, $event->getAnnouncementId());
        $historicAnnouncement = HistoricAnnouncement::create($announcement);
        $this->entityManager->persist($historicAnnouncement);

        $this->logger->debug("HistoricAnnouncement announcement created",
            array ("historicEntry" => $historicAnnouncement));
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
        $announcement = $this->entityManager->find(Announcement::class, $event->getAnnouncementId());
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
        /** @var UserDto $userDto */
        $userDto = $this->userDtoMapper->toDto($user);

        $this->logger->debug("Sending an e-mail to a user", array ("user" => $user));

        $subject = $this->translator->trans("text.mail.announcement.deletion.subject",
            array ("%title%" => $announcement->getTitle()));

        $this->sendMail($userDto, $subject, array ("announcement" => $announcement, "candidate" => $user));
    }


    protected function getMailTemplate() : string
    {
        return self::DELETION_MAIL_TEMPLATE;
    }

}