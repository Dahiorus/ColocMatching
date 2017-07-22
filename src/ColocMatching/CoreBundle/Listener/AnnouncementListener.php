<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementManagerInterface;
use Doctrine\ORM\Mapping\PreRemove;
use Psr\Log\LoggerInterface;

/**
 * Event listener for announcement.
 *
 * @author Dahiorus
 */
class AnnouncementListener {

    /**
     * @var HistoricAnnouncementManagerInterface
     */
    private $historicAnnouncementManager;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(HistoricAnnouncementManagerInterface $historicAnnouncementManager,
        LoggerInterface $logger) {
        $this->historicAnnouncementManager = $historicAnnouncementManager;
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

        $this->logger->info("HistoricAnnouncement announcement created", array ("historicAnnouncement" => $historicAnnouncement));
    }

}