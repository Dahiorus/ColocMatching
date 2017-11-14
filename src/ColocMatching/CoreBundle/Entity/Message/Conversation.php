<?php

namespace ColocMatching\CoreBundle\Entity\Message;

use ColocMatching\CoreBundle\Entity\Updatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class representing an abstract conversation
 *
 * @ORM\MappedSuperclass
 * @ORM\EntityListeners({ "ColocMatching\CoreBundle\Listener\UpdatableListener" })
 *
 * @author Dahiorus
 */
abstract class Conversation implements Updatable {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Collection
     */
    protected $messages;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_update", type="datetime")
     */
    protected $lastUpdate;


    public function __construct() {
        $this->messages = new ArrayCollection();
    }


    public function getId() : int {
        return $this->id;
    }


    public function setId(int $id) {
        $this->id = $id;

        return $this;
    }


    public function getCreatedAt() : \DateTime {
        return $this->createdAt;
    }


    public function setCreatedAt(\DateTime $createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }


    public function getLastUpdate() : \DateTime {
        return $this->lastUpdate;
    }


    public function setLastUpdate(\DateTime $lastUpdate) {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }


    public function getMessages() : Collection {
        return $this->messages;
    }


    public function setMessages(Collection $messages) {
        $this->messages = $messages;

        return $this;
    }


    public function addMessage(Message $message) {
        $this->messages->add($message);

        return $this;
    }


    public function removeMessage(Message $message) {
        $this->messages->removeElement($message);
    }

}