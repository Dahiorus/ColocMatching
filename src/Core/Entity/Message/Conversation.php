<?php

namespace App\Core\Entity\Message;

use App\Core\Entity\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class representing an abstract conversation
 *
 * @ORM\MappedSuperclass
 *
 * @author Dahiorus
 */
abstract class Conversation extends AbstractEntity
{
    /**
     * @var Collection
     */
    protected $messages;


    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }


    public function getMessages() : Collection
    {
        return $this->messages;
    }


    public function setMessages(Collection $messages)
    {
        $this->messages = $messages;

        return $this;
    }


    public function addMessage(Message $message)
    {
        $this->messages->add($message);
        $message->setConversation($this);

        return $this;
    }


    public function removeMessage(Message $message)
    {
        $this->messages->removeElement($message);
    }

}
