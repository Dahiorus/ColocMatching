<?php

namespace ColocMatching\CoreBundle\Manager\Visit;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\UserVisit;

class UserVisitDtoManager extends VisitDtoManager
{
    protected function getDomainClass() : string
    {
        return UserVisit::class;
    }


    protected function getVisitedClass() : string
    {
        return User::class;
    }

}
