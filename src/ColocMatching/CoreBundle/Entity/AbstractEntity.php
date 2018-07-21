<?php

namespace ColocMatching\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\EntityListeners({
 *   "ColocMatching\CoreBundle\Listener\UpdateListener",
 *   "ColocMatching\CoreBundle\Listener\CacheDriverListener"
 * })
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
     * @var \DateTimeImmutable
     * @ORM\Column(name="created_at", type="datetime_immutable")
     */
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
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }


    public function setCreatedAt(\DateTimeImmutable $createdAt = null)
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
        try
        {
            $reflectionClass = new \ReflectionClass($this);
            $className = $reflectionClass->getShortName();
        }
        catch (\ReflectionException $e)
        {
            $className = get_class($this);
        }

        $createdAt = empty($this->createdAt) ? null : $this->createdAt->format(\DateTime::ISO8601);
        $lastUpdate = empty($this->lastUpdate) ? null : $this->lastUpdate->format(\DateTime::ISO8601);

        return $className . "[id = $this->id, createdAt = $createdAt, lastUpdate = $lastUpdate]";
    }
}