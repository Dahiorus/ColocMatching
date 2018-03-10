<?php

namespace ColocMatching\CoreBundle\Manager\Visit;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Visit\GroupVisit;

class GroupVisitDtoManager extends VisitDtoManager
{
    protected function getDomainClass() : string
    {
        return GroupVisit::class;
    }


    protected function getVisitedClass() : string
    {
        return Group::class;
    }

}
