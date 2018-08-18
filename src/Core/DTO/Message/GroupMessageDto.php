<?php

namespace App\Core\DTO\Message;

use App\Core\DTO\Annotation\RelatedEntity;
use App\Core\Entity\Group\Group;
use App\Core\Entity\Message\GroupMessage;

class GroupMessageDto extends MessageDto
{
    /**
     * The group identifier
     * @var int
     *
     * @RelatedEntity(targetClass=Group::class, targetProperty="group")
     */
    private $groupId;


    public function getGroupId()
    {
        return $this->groupId;
    }


    public function setGroupId(int $groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }


    public function getEntityClass() : string
    {
        return GroupMessage::class;
    }

}