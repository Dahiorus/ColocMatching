<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

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
     * @var \DateTime
     */
    private $createdAtSince;

    /**
     * @var \DateTime
     */
    private $createdAtUntil;

    /**
     * @var ProfileFilter
     */
    private $profileFilter;


    public function __toString() : string
    {
        $createdAtSince = empty($this->createdAtSince) ? null : $this->createdAtSince->format(\DateTime::ISO8601);
        $createdAtUntil = empty($this->createdAtUntil) ? null : $this->createdAtUntil->format(\DateTime::ISO8601);

        return get_class($this) . " [type='" . $this->type . "', hasAnnouncement=" . $this->hasAnnouncement
            . ", hasGroup=" . $this->hasGroup . ", status=[" . implode(",", $this->status)
            . "], createdAtSince=" . $createdAtSince . ", createdAtUntil=" . $createdAtUntil
            . ", profileFilter= " . $this->profileFilter . "]";
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


    public function getProfileFilter()
    {
        return $this->profileFilter;
    }


    public function setProfileFilter(ProfileFilter $profileFilter = null)
    {
        $this->profileFilter = $profileFilter;

        return $this;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Repository\Filter\AbstractFilter::buildCriteria()
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