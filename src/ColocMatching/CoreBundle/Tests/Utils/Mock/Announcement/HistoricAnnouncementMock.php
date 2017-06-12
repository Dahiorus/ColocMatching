<?php

namespace ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;

class HistoricAnnouncementMock {


    public static function createHistoricAnnouncement(int $id, Announcement $announcement): HistoricAnnouncement {
        $historicAnnouncement = new HistoricAnnouncement($announcement);

        $historicAnnouncement->setId($id);

        return $historicAnnouncement;
    }


    public static function createHistoricAnnouncementPage(PageableFilter $filter, int $total): array {
        $historicAnnouncements = array ();
        $announcements = AnnouncementMock::createAnnouncementPage($filter, $total);

        foreach ($announcements as $announcement) {
            $historicAnnouncements[] = self::createHistoricAnnouncement($announcement->getId(), $announcement);
        }

        return $historicAnnouncements;
    }


    private function __construct() {
        // empty constructor
    }

}