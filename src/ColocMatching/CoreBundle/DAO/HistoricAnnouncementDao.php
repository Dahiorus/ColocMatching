<?php

namespace ColocMatching\CoreBundle\DAO;

use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;

class HistoricAnnouncementDao extends EntityDao
{
    protected function getDomainClass() : string
    {
        return HistoricAnnouncement::class;
    }

}