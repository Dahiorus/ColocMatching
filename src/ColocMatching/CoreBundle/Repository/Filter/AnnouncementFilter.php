<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use Doctrine\Common\Collections\Criteria;

/**
 * Announcement query filter class
 *
 * @author brondon.ung
 */
class AnnouncementFilter extends AbstractAnnouncementFilter
{
    /**
     * @var boolean
     */
    private $withDescription = false;

    /**
     * @var boolean
     */
    private $withPictures = false;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var \DateTime
     */
    private $createdAtSince;

    /**
     * @var HousingFilter
     */
    private $housingFilter;


    public function __toString() : string
    {
        $createdAtSince = empty($this->createdAtSince) ? null : $this->createdAtSince->format(\DateTime::ISO8601);

        return parent::__toString() . "[withDescription=" . $this->withDescription . ", status='" . $this->status
            . ", withPictures=" . $this->withPictures . ", createdAtSince=" . $createdAtSince . "]";
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


    public function getCreatedAtSince()
    {
        return $this->createdAtSince;
    }


    public function setCreatedAtSince(\DateTime $createdAtSince = null)
    {
        $this->createdAtSince = $createdAtSince;

        return $this;
    }


    public function getHousingFilter()
    {
        return $this->housingFilter;
    }


    public function setHousingFilter(HousingFilter $housingFilter = null)
    {
        $this->housingFilter = $housingFilter;

        return $this;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Repository\Filter\AbstractFilter::buildCriteria()
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

        return $criteria;
    }

}