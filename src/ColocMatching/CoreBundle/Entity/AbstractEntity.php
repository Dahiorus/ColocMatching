<?php

namespace ColocMatching\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\EntityListeners({})
 */
abstract class AbstractEntity implements EntityInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    // TODO createdAt not nullable
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="last_update", type="datetime", nullable=true)
     */
    protected $lastUpdate;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    public function setId(?int $id)
    {
        $this->id = $id;

        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }


    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }


    public function setLastUpdate(\DateTime $lastUpdate = null)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }


    public function __toString()
    {
        $createdAt = empty($this->createdAt) ? null : $this->createdAt->format(\DateTime::ISO8601);
        $lastUpdate = empty($this->lastUpdate) ? null : $this->lastUpdate->format(\DateTime::ISO8601);

        return get_class($this) . "[id = $this->id, createdAt = $createdAt, lastUpdate = $lastUpdate]";
    }
}