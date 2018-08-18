<?php

namespace App\Core\DTO\Message;

use App\Core\DTO\AbstractDto;
use App\Core\Entity\Message\GroupConversation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class GroupConversationDto extends AbstractDto
{
    /**
     * @var int
     */
    private $groupId;

    /**
     * @var Collection<GroupMessageDto>
     */
    private $messages;


    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }


    public function __toString() : string
    {
        return parent::__toString() . "[groupId = " . $this->groupId . "]";
    }


    public function getGroupId()
    {
        return $this->groupId;
    }


    public function setGroupId(int $groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }


    public function getMessages() : Collection
    {
        return $this->messages;
    }


    public function setMessages(Collection $messages = null)
    {
        $this->messages = $messages;

        return $this;
    }


    public function getEntityClass() : string
    {
        return GroupConversation::class;
    }

}