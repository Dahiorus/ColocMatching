<?php

namespace ColocMatching\CoreBundle\Entity\Message;

use ColocMatching\CoreBundle\Entity\AbstractEntity;
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
        $message->setParent($this->messages->last() ?: null);
        $this->messages->add($message);
        $message->setConversation($this);

        return $this;
    }


    public function removeMessage(Message $message)
    {
        $this->messages->filter(function (Message $m) use ($message) {
            return $m->getParent() === $message;
        })->forAll(function (Message $m) use ($message) {
            $m->setParent($message->getParent());
        });
        $this->messages->removeElement($message);
    }

}