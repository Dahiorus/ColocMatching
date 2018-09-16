<?php

namespace App\Core\Repository\Filter;

use Doctrine\Common\Collections\Criteria;

/**
 * User query filter class
 *
 * @author Dahiorus
 */
class UserFilter extends AbstractPageableFilter implements Searchable
{

    /**
     * @var string
     */
    private $type;

    /**
     * @var boolean
     */
    private $hasAnnouncement = false;

    /**
     * @var boolean
     */
    private $hasGroup = false;

    /**
     * @var array
     */
    private $status = array ();

    /**
     * @var string
     */
    private $gender;

    /**
     * @var integer
     */
    private $ageStart;

    /**
     * @var integer
     */
    private $ageEnd;

    /**
     * @var boolean
     */
    private $withDescription = false;

    /**
     * @var \DateTime
     */
    private $createdAtSince;

    /**
     * @var \DateTime
     */
    private $createdAtUntil;


    public function __toString() : string
    {
        $createdAtSince = empty($this->createdAtSince) ? null : $this->createdAtSince->format(\DateTime::ISO8601);
        $createdAtUntil = empty($this->createdAtUntil) ? null : $this->createdAtUntil->format(\DateTime::ISO8601);

        return get_class($this) . " [type='" . $this->type . "', hasAnnouncement=" . $this->hasAnnouncement
            . ", hasGroup=" . $this->hasGroup . ", status=[" . implode(",", $this->status)
            . ", gender=" . $this->gender . ", ageStart=" . $this->ageStart . ", ageEnd=" . $this->ageEnd
            . ", withDescription=" . $this->withDescription . "], createdAtSince=" . $createdAtSince
            . ", createdAtUntil=" . $createdAtUntil . "]";
    }


    public function getType()
    {
        return $this->type;
    }


    public function setType(?string $type)
    {
        $this->type = $type;

        return $this;
    }


    public function hasAnnouncement()
    {
        return $this->hasAnnouncement;
    }


    public function setHasAnnouncement(?bool $hasAnnouncement)
    {
        $this->hasAnnouncement = $hasAnnouncement;

        return $this;
    }


    public function hasGroup()
    {
        return $this->hasGroup;
    }


    public function setHasGroup(?bool $hasGroup)
    {
        $this->hasGroup = $hasGroup;

        return $this;
    }


    public function getStatus()
    {
        return $this->status;
    }


    public function setStatus(array $status = null)
    {
        $this->status = $status;

        return $this;
    }


    public function getGender()
    {
        return $this->gender;
    }


    public function setGender(?string $gender)
    {
        $this->gender = $gender;

        return $this;
    }


    public function getAgeStart()
    {
        return $this->ageStart;
    }


    public function setAgeStart(?int $ageStart)
    {
        $this->ageStart = $ageStart;

        return $this;
    }


    public function getAgeEnd()
    {
        return $this->ageEnd;
    }


    public function setAgeEnd(?int $ageEnd)
    {
        $this->ageEnd = $ageEnd;

        return $this;
    }


    public function isWithDescription()
    {
        return $this->withDescription;
    }


    public function setWithDescription(bool $withDescription = false)
    {
        $this->withDescription = $withDescription;

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


    public function getCreatedAtUntil()
    {
        return $this->createdAtUntil;
    }


    public function setCreatedAtUntil(\DateTime $createdAtUntil = null)
    {
        $this->createdAtUntil = $createdAtUntil;

        return $this;
    }


    /**
     * {@inheritDoc}
     * @see \App\Core\Repository\Filter\AbstractFilter::buildCriteria()
     */
    public function buildCriteria() : Criteria
    {
        /** @var Criteria */
        $criteria = Criteria::create();

        if (!empty($this->type))
        {
            $criteria->andWhere(Criteria::expr()->eq("type", $this->type));
        }

        if (!empty($this->status))
        {
            $criteria->andWhere(Criteria::expr()->in("status", $this->status));
        }

        if (!empty($this->gender))
        {
            $criteria->andWhere(Criteria::expr()->eq("gender", $this->gender));
        }

        if (!empty($this->ageStart))
        {
            $ageStart = $this->ageStart;
            $criteria->andWhere(Criteria::expr()->lte("ageStart", new \DateTime("-$ageStart years")));
        }

        if (!empty($this->ageEnd))
        {
            $ageEnd = $this->ageEnd;
            $criteria->andWhere(Criteria::expr()->gte("ageStart", new \DateTime("-$ageEnd years")));
        }

        if ($this->withDescription)
        {
            $criteria->andWhere(Criteria::expr()->neq("description", null));
        }

        if (!empty($this->createdAtSince))
        {
            $criteria->andWhere(Criteria::expr()->gte("createdAt", $this->createdAtSince));
        }

        if (!empty($this->createdAtUntil))
        {
            $criteria->andWhere(Criteria::expr()->lte("createdAt", $this->createdAtUntil));
        }

        return $criteria;
    }

}