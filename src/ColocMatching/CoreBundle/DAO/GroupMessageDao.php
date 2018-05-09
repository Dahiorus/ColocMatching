<?php

namespace ColocMatching\CoreBundle\DAO;

use ColocMatching\CoreBundle\DTO\Message\GroupMessageDto;

class GroupMessageDao extends EntityDao
{
    protected function getDomainClass() : string
    {
        return GroupMessageDto::class;
    }

}