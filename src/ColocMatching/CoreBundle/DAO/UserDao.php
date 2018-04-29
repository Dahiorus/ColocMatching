<?php

namespace ColocMatching\CoreBundle\DAO;

use ColocMatching\CoreBundle\Entity\User\User;

class UserDao extends EntityDao
{
    protected function getDomainClass() : string
    {
        return User::class;
    }

}
