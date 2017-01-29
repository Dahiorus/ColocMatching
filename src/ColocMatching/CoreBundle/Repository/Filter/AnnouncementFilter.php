<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Announcement query filter class
 *
 * @author brondon.ung
 */
class AnnouncementFilter extends AbstractFilter {

    /**
     * @var Address
     */
    private $address;

    /**
     * @var int
     */
    private $minPriceStart;

    /**
     * @var int
     */
    private $minPriceEnd;

    /**
     * @var int
     */
    private $maxPriceStart;

    /**
     * @var int
     */
    private $maxPriceEnd;

    /**
     * @var array
     * @Assert\Choice(choices={Announcement::TYPE_RENT, Announcement::TYPE_SUBLEASE, Announcement::TYPE_SHARING},
     *   multiple=true, strict=true)
     */
    private $types = [ ];

    /**
     * @var \DateTime
     * @Assert\Date()
     */
    private $startDateAfter;

    /**
     * @var \DateTime
     * @Assert\Date()
     */
    private $startDateBefore;

    /**
     * @var \DateTime
     * @Assert\Date()
     */
    private $endDateAfter;

    /**
     * @var \DateTime
     * @Assert\Date()
     */
    private $endDateBefore;

    /**
     * @var string
     * @Assert\Choice(choices={UserConstants::TYPE_SEARCH, UserConstants::TYPE_PROPOSAL}, strict=true)
     */
    private $creatorType;


    public function getAddress() {
        return $this->address;
    }


    public function setAddress(Address $address = null) {
        $this->address = $address;
        return $this;
    }


    public function getMinPriceStart() {
        return $this->minPriceStart;
    }


    public function setMinPriceStart(int $minPriceStart = null) {
        $this->minPriceStart = $minPriceStart;
        return $this;
    }


    public function getMinPriceEnd() {
        return $this->minPriceEnd;
    }


    public function setMinPriceEnd(int $minPriceEnd = null) {
        $this->minPriceEnd = $minPriceEnd;
        return $this;
    }


    public function getMaxPriceStart() {
        return $this->maxPriceStart;
    }


    public function setMaxPriceStart(int $maxPriceStart = null) {
        $this->maxPriceStart = $maxPriceStart;
        return $this;
    }


    public function getMaxPriceEnd() {
        return $this->maxPriceEnd;
    }


    public function setMaxPriceEnd(int $maxPriceEnd = null) {
        $this->maxPriceEnd = $maxPriceEnd;
        return $this;
    }


    public function getTypes() {
        return $this->types;
    }


    public function setTypes(array $types = null) {
        $this->types = $types;
        return $this;
    }


    public function getStartDateAfter() {
        return $this->startDateAfter;
    }


    public function setStartDateAfter(\DateTime $startDateAfter = null) {
        $this->startDateAfter = $startDateAfter;
        return $this;
    }


    public function getStartDateBefore() {
        return $this->startDateBefore;
    }


    public function setStartDateBefore(\DateTime $startDateBefore = null) {
        $this->startDateBefore = $startDateBefore;
        return $this;
    }


    public function getEndDateAfter() {
        return $this->endDateAfter;
    }


    public function setEndDateAfter(\DateTime $endDateAfter = null) {
        $this->endDateAfter = $endDateAfter;
        return $this;
    }


    public function getEndDateBefore() {
        return $this->endDateBefore;
    }


    public function setEndDateBefore(\DateTime $endDateBefore = null) {
        $this->endDateBefore = $endDateBefore;
        return $this;
    }


    public function getCreatorType() {
        return $this->creatorType;
    }


    public function setCreatorType(string $creatorType = null) {
        $this->creatorType = $creatorType;
        return $this;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Repository\Filter\AbstractFilter::buildCriteria()
     */
    public function buildCriteria(): Criteria {
        /** @var Criteria */
        $criteria = Criteria::create();
        
        if (!empty($this->minPriceStart)) {
            $criteria->andWhere($criteria->expr()->gte("minPrice", $this->minPriceStart));
        }
        
        if (!empty($this->minPriceEnd)) {
            $criteria->andWhere($criteria->expr()->lte("minPrice", $this->minPriceEnd));
        }
        
        if (!empty($this->maxPriceStart)) {
            $criteria->andWhere($criteria->expr()->gte("maxPrice", $this->maxPriceStart));
        }
        
        if (!empty($this->maxPriceEnd)) {
            $criteria->andWhere($criteria->expr()->lte("maxPrice", $this->maxPriceEnd));
        }
        
        if (!empty($this->types)) {
            $criteria->andWhere($criteria->expr()->in("type", $this->types));
        }
        
        if (!empty($this->startDateAfter)) {
            $criteria->andWhere($criteria->expr()->lte("startDate", $this->startDateAfter));
        }
        
        if (!empty($this->startDateBefore)) {
            $criteria->andWhere($criteria->expr()->gte("startDate", $this->startDateBefore));
        }
        
        if (!empty($this->endDateAfter)) {
            $criteria->andWhere($criteria->expr()->lte("endDate", $this->endDateAfter));
        }
        
        if (!empty($this->endDateBefore)) {
            $criteria->andWhere($criteria->expr()->gte("endDate", $this->endDateBefore));
        }
        
        return $criteria;
    }

}