<?php

namespace ColocMatching\CoreBundle\Manager\Announcement;

use ColocMatching\CoreBundle\Manager\ManagerInterface;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;
use ColocMatching\CoreBundle\Repository\Filter\HistoricAnnouncementFilter;

interface HistoricAnnouncementManagerInterface extends ManagerInterface {


    /**
     * Creates an HistoricAnnouncement from an Announcement
     *
     * @param Announcement $announcement The announcement to add to history
     * @param bool $flush Flushing persistence or not
     * @return HistoricAnnouncement
     */
    public function create(Announcement $announcement, bool $flush = false): HistoricAnnouncement;


    /**
     * Searches historic announcements corresponding to the filter
     *
     * @param HistoricAnnouncementFilter $filter The search filter
     * @param array $fields The fields to return
     * @return array
     */
    public function search(HistoricAnnouncementFilter $filter, array $fields = null): array;


    /**
     * Counts instances corresponding to the filter
     *
     * @param HistoricAnnouncementFilter $filter The search filter
     * @return int
     */
    public function countBy(HistoricAnnouncementFilter $filter): int;

}