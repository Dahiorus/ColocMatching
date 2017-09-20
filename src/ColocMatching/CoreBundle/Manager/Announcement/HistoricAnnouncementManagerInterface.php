<?php

namespace ColocMatching\CoreBundle\Manager\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;
use ColocMatching\CoreBundle\Manager\ManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\HistoricAnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;

interface HistoricAnnouncementManagerInterface extends ManagerInterface {

    /**
     * Creates an HistoricAnnouncement from an Announcement
     *
     * @param Announcement $announcement The announcement to add to history
     * @param bool $flush                Flushing persistence or not
     *
     * @return HistoricAnnouncement
     */
    public function create(Announcement $announcement, bool $flush = false) : HistoricAnnouncement;


    /**
     * Searches historic announcements corresponding to the filter
     *
     * @param HistoricAnnouncementFilter $filter The search filter
     * @param array $fields                      The fields to return
     *
     * @return array
     */
    public function search(HistoricAnnouncementFilter $filter, array $fields = null) : array;


    /**
     * Counts instances corresponding to the filter
     *
     * @param HistoricAnnouncementFilter $filter The search filter
     *
     * @return int
     */
    public function countBy(HistoricAnnouncementFilter $filter) : int;


    /**
     * Gets the comments of an announcement with pagination
     *
     * @param HistoricAnnouncement $announcement The announcement from witch get the comments
     * @param PageableFilter $filter             Pagination information
     *
     * @return array
     */
    public function getComments(HistoricAnnouncement $announcement, PageableFilter $filter) : array;

}