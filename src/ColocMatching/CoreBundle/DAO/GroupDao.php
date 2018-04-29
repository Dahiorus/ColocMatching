<?php

namespace ColocMatching\CoreBundle\DAO;

use ColocMatching\CoreBundle\Entity\Group\Group;

class GroupDao extends EntityDao
{
    protected function getDomainClass() : string
    {
        return Group::class;
    }

}
