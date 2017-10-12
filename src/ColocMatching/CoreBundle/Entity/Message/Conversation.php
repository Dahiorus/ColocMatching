<?php

namespace ColocMatching\CoreBundle\Entity\Message;

use ColocMatching\CoreBundle\Entity\Updatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * Class representing an abstract conversation
 *
 * @ORM\MappedSuperclass
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Conversation")
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
     * @Serializer\Expose()
     * @SWG\Property(description="Conversation identifier", readOnly=true)
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
     * @Serializer\Type(name="DateTime<'Y-m-d\TH:i:s'>")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_update", type="datetime")
     * @Serializer\Type(name="DateTime<'Y-m-d\TH:i:s'>")
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