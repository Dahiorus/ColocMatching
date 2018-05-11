<?php

namespace ColocMatching\CoreBundle\DTO\Message;

use ColocMatching\CoreBundle\DTO\Annotation\RelatedEntity;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Message\GroupMessage;

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