<?php

namespace ColocMatching\CoreBundle\DTO;

use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="AbstractDto")
 */
abstract class AbstractDto
{
    /**
     * Entity identifier
     * @var integer
     * @Serializer\Expose
     * @SWG\Property(readOnly=true, example="1")
     */
    protected $id;

    /**
     * Entity creation date time
     * @var \DateTimeImmutable
     * @Serializer\Expose
     * @Serializer\SerializedName("createdAt")
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:s'>")
     * @SWG\Property(readOnly=true)
     */
    protected $createdAt;

    /**
     * Entity last update date time
     * @var \DateTime
     * @Serializer\Expose
     * @Serializer\SerializedName("lastUpdate")
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:s'>")
     * @SWG\Property(readOnly=true)
     */
    protected $lastUpdate;


    /**
     * @return int|null
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
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }


    /**
     * @param \DateTimeImmutable|null $createdAt
     *
     * @return AbstractDto
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt = null) : AbstractDto
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


    /**
     * @param \DateTime|null $lastUpdate
     *
     * @return AbstractDto
     */
    public function setLastUpdate(\DateTime $lastUpdate = null) : AbstractDto
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }


    public function __toString() : string
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


    /**
     * Returns the entity class associated with this DTO
     * @return string
     */
    abstract public function getEntityClass() : string;
}