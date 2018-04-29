<?php

namespace ColocMatching\CoreBundle\DAO;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;

class AnnouncementDao extends EntityDao
{
    protected function getDomainClass() : string
    {
        return Announcement::class;
    }

}
