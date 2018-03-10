<?php

namespace ColocMatching\CoreBundle\Manager\Visit;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Visit\AnnouncementVisit;

class AnnouncementVisitDtoManager extends VisitDtoManager
{
    protected function getDomainClass() : string
    {
        return AnnouncementVisit::class;
    }


    protected function getVisitedClass() : string
    {
        return Announcement::class;
    }

}
