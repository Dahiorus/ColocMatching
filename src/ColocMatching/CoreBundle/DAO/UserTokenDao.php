<?php

namespace ColocMatching\CoreBundle\DAO;

use ColocMatching\CoreBundle\Entity\User\UserToken;

class UserTokenDao extends EntityDao
{
    protected function getDomainClass() : string
    {
        return UserToken::class;
    }

}
