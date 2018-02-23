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
     * @var \DateTime
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
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }


    /**
     * @param \DateTime|null $createdAt
     *
     * @return AbstractDto
     */
    public function setCreatedAt(\DateTime $createdAt = null) : AbstractDto
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
        $createdAt = empty($this->createdAt) ? null : $this->createdAt->format(DATE_ISO8601);
        $lastUpdate = empty($this->lastUpdate) ? null : $this->lastUpdate->format(DATE_ISO8601);

        return get_class($this) . "[id = $this->id, createdAt = $createdAt, lastUpdate = $lastUpdate]";
    }


    /**
     * Returns the entity class associated with this DTO
     * @return string
     */
    abstract public function getEntityClass() : string;
}