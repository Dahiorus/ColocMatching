<?php

namespace App\Core\Repository\Filter;

use Doctrine\Common\Collections\Criteria;
use JMS\Serializer\Annotation as Serializer;

/**
 * Announcement query filter class
 *
 * @author Dahiorus
 */
class AnnouncementFilter extends AbstractAnnouncementFilter
{
    /**
     * @var boolean
     * @Serializer\Type("bool")
     */
    private $withDescription = false;

    /**
     * @var boolean
     * @Serializer\Type("bool")
     */
    private $withPictures = false;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $status;

    /**
     * @var string[]
     * @Serializer\Type("array<string>")
     */
    private $housingTypes = array ();

    /**
     * @var integer
     * @Serializer\Type("int")
     */
    private $roomCount;

    /**
     * @var integer
     * @Serializer\Type("int")
     */
    private $bedroomCount;

    /**
     * @var integer
     * @Serializer\Type("int")
     */
    private $bathroomCount;

    /**
     * @var integer
     * @Serializer\Type("int")
     */
    private $surfaceAreaMin;

    /**
     * @var integer
     * @Serializer\Type("int")
     */
    private $surfaceAreaMax;

    /**
     * @var integer
     * @Serializer\Type("int")
     */
    private $roomMateCount;

    /**
     * @var \DateTime
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:s'>")
     */
    private $createdAtSince;


    public function __toString() : string
    {
        $createdAtSince = empty($this->createdAtSince) ? null : $this->createdAtSince->format(\DateTime::ISO8601);

        return parent::__toString() . "[withDescription=" . $this->withDescription . ", status='" . $this->status
            . ", withPictures=" . $this->withPictures . ", housingTypes={" . implode(", ", $this->housingTypes) . "}"
            . ", roomCount=" . $this->roomCount . ", bedroomCount=" . $this->bedroomCount
            . ", bathroomCount=" . $this->bathroomCount . "surfaceAreaMin=" . $this->surfaceAreaMin
            . ", surfaceAreaMax=" . $this->surfaceAreaMax . ", roomMateCount=" . $this->roomMateCount
            . ", createdAtSince=" . $createdAtSince . "]";
    }


    public function isWithDescription()
    {
        return $this->withDescription;
    }


    public function setWithDescription(?bool $withDescription)
    {
        $this->withDescription = $withDescription;

        return $this;
    }


    public function withPictures()
    {
        return $this->withPictures;
    }


    public function setWithPictures(?bool $withPictures)
    {
        $this->withPictures = $withPictures;

        return $this;
    }


    public function getStatus()
    {
        return $this->status;
    }


    public function setStatus(?string $status)
    {
        $this->status = $status;

        return $this;
    }


    public function getHousingTypes()
    {
        return $this->housingTypes;
    }


    public function setHousingTypes(?array $housingTypes)
    {
        $this->housingTypes = $housingTypes;

        return $this;
    }


    public function getRoomCount()
    {
        return $this->roomCount;
    }


    public function setRoomCount(?int $roomCount)
    {
        $this->roomCount = $roomCount;

        return $this;
    }


    public function getBedroomCount()
    {
        return $this->bedroomCount;
    }


    public function setBedroomCount(?int $bedroomCount)
    {
        $this->bedroomCount = $bedroomCount;

        return $this;
    }


    public function getBathroomCount()
    {
        return $this->bathroomCount;
    }


    public function setBathroomCount(?int $bathroomCount)
    {
        $this->bathroomCount = $bathroomCount;

        return $this;
    }


    public function getSurfaceAreaMin()
    {
        return $this->surfaceAreaMin;
    }


    public function setSurfaceAreaMin(?int $surfaceAreaMin)
    {
        $this->surfaceAreaMin = $surfaceAreaMin;

        return $this;
    }


    public function getSurfaceAreaMax()
    {
        return $this->surfaceAreaMax;
    }


    public function setSurfaceAreaMax(?int $surfaceAreaMax)
    {
        $this->surfaceAreaMax = $surfaceAreaMax;

        return $this;
    }


    public function getRoomMateCount()
    {
        return $this->roomMateCount;
    }


    public function setRoomMateCount(?int $roomMateCount)
    {
        $this->roomMateCount = $roomMateCount;

        return $this;
    }


    public function getCreatedAtSince()
    {
        return $this->createdAtSince;
    }


    public function setCreatedAtSince(\DateTime $createdAtSince = null)
    {
        $this->createdAtSince = $createdAtSince;

        return $this;
    }


    /**
     * {@inheritDoc}
     * @see \App\Core\Repository\Filter\AbstractFilter::buildCriteria()
     */
    public function buildCriteria() : Criteria
    {
        /** @var Criteria */
        $criteria = parent::buildCriteria();

        if ($this->withDescription)
        {
            $criteria->andWhere(Criteria::expr()->neq("description", null));
        }

        if (!empty($this->status))
        {
            $criteria->andWhere(Criteria::expr()->eq("status", $this->status));
        }

        if (!empty($this->createdAtSince))
        {
            $criteria->andWhere(Criteria::expr()->gte("createdAt", $this->createdAtSince));
        }

        if (!empty($this->housingTypes))
        {
            $criteria->andWhere($criteria->expr()->in("type", $this->housingTypes));
        }

        if (!empty($this->roomCount))
        {
            $criteria->andWhere($criteria->expr()->eq("roomCount", $this->roomCount));
        }

        if (!empty($this->bedroomCount))
        {
            $criteria->andWhere($criteria->expr()->eq("bedroomCount", $this->bedroomCount));
        }

        if (!empty($this->bathroomCount))
        {
            $criteria->andWhere($criteria->expr()->eq("bathroomCount", $this->bathroomCount));
        }

        if (!empty($this->surfaceAreaMin))
        {
            $criteria->andWhere($criteria->expr()->gte("surfaceArea", $this->surfaceAreaMin));
        }

        if (!empty($this->surfaceAreaMax))
        {
            $criteria->andWhere($criteria->expr()->lte("surfaceArea", $this->surfaceAreaMax));
        }

        if (!empty($this->roomMateCount))
        {
            $criteria->andWhere($criteria->expr()->eq("roomMateCount", $this->roomMateCount));
        }

        return $criteria;
    }

}