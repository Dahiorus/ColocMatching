<?php

namespace ColocMatching\RestBundle\Listener;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;
use ColocMatching\CoreBundle\Listener\MailerListener;
use ColocMatching\CoreBundle\Manager\Visit\AnnouncementVisitDtoManager;
use ColocMatching\MailBundle\Service\MailSenderInterface;
use ColocMatching\RestBundle\Event\DeleteAnnouncementEvent;
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

    /** @var AnnouncementVisitDtoManager */
    private $visitManager;


    public function __construct(LoggerInterface $logger, MailSenderInterface $mailSender,
        TranslatorInterface $translator, string $from, EntityManagerInterface $entityManager,
        AnnouncementVisitDtoManager $visitManager)
    {
        parent::__construct($mailSender, $translator, $from, $logger);
        $this->entityManager = $entityManager;
        $this->visitManager = $visitManager;
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
    public function onDeleteEvent(DeleteAnnouncementEvent $event)
    {
        $dto = $event->getAnnouncement();

        $this->createHistoricEntry($dto);
        $this->sendMailToCandidates($dto, $event->getCandidates());
        $this->visitManager->deleteVisitableVisits($dto->getId(), false);
    }


    /**
     * Creates an historic announcement to save the announcement in history
     *
     * @param AnnouncementDto $dto The announcement to delete
     */
    private function createHistoricEntry(AnnouncementDto $dto)
    {
        $this->logger->info("Creating a historic entry of an announcement",
            array ("announcement" => $dto));

        /** @var Announcement $announcement */
        $announcement = $this->entityManager->find($dto->getEntityClass(), $dto->getId());
        $historicAnnouncement = HistoricAnnouncement::create($announcement);
        $this->entityManager->persist($historicAnnouncement);

        $this->logger->debug("HistoricAnnouncement announcement created",
            array ("historicEntry" => $historicAnnouncement));
    }


    /**
     * Callback event before the announcement is deleted.
     * Sends an e-mail to all candidates to inform them of the deletion
     *
     * @param AnnouncementDto $announcement The deleted announcement
     * @param UserDto[] $candidates The candidates to inform
     */
    private function sendMailToCandidates(AnnouncementDto $announcement, array $candidates)
    {
        $this->logger->info("Sending an e-mail to all candidates of an announcement",
            array ("candidates" => $announcement));

        array_walk($candidates, function (UserDto $u) use ($announcement) {
            $this->sendMailToCandidate($u, $announcement);
        });

        $this->logger->debug(sprintf("%d mails sent", count($candidates)));
    }


    /**
     * Sends an e-mail informing of the deletion of an announcement to a candidate
     *
     * @param UserDto $user The candidate of the announcement
     * @param AnnouncementDto $announcement The announcement to be deleted
     */
    private function sendMailToCandidate(UserDto $user, AnnouncementDto $announcement)
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