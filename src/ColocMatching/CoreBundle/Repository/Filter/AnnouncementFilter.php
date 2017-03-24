<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use Doctrine\Common\Collections\Criteria;
use Swagger\Annotations as SWG;

/**
 * Announcement query filter class
 *
 * @SWG\Definition(definition="AnnouncementFilter")
 *
 * @author brondon.ung
 */
class AnnouncementFilter extends AbstractFilter {

    /**
     * @var Address
     * @SWG\Property(type="string", description="Location filter")
     */
    private $address;

    /**
     * @var integer
     * @SWG\Property(description="Rent price start range filter")
     */
    private $rentPriceStart;

    /**
     * @var integer
     * @SWG\Property(description="Rent price end range filter")
     */
    private $rentPriceEnd;

    /**
     * @var array
     *
     * @SWG\Property(description="Announcement types filter")
     */
    private $types = [ ];

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="Start date 'from' filter", format="date")
     */
    private $startDateAfter;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="Start date 'to' filter", format="date")
     */
    private $startDateBefore;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="End date 'from' filter", format="date")
     */
    private $endDateAfter;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="End date 'to' filter", format="date")
     */
    private $endDateBefore;

    /**
     * @var string
     *
     * @SWG\Property(description="Creator type filter")
     */
    private $creatorType;


    public function __toString(): string {
        $startDateAfter = empty($this->startDateAfter) ? "" : $this->startDateAfter->format(\DateTime::ISO8601);
        $startDateBefore = empty($this->startDateBefore) ? "" : $this->startDateBefore->format(\DateTime::ISO8601);
        $endDateAfter = empty($this->endDateAfter) ? "" : $this->endDateAfter->format(\DateTime::ISO8601);
        $endDateBefore = empty($this->endDateBefore) ? "" : $this->endDateBefore->format(\DateTime::ISO8601);

        return sprintf(
            "AnnouncementFilter[%s] [address: %s, rentPrice: [%d - %d], types: [%s], startDate: ['%s' - '%s'], endDate: ['%s' - '%s'], creatorType: '%s']",
            parent::__toString(), $this->address, $this->rentPriceStart, $this->rentPriceEnd,
            implode(", ", $this->types), $startDateAfter, $startDateBefore, $endDateAfter, $endDateBefore,
            $this->creatorType);
    }


    public function getAddress() {
        return $this->address;
    }


    public function setAddress(Address $address = null) {
        $this->address = $address;
        return $this;
    }


    public function getRentPriceStart() {
        return $this->rentPriceStart;
    }


    public function setRentPriceStart(int $rentPriceStart = null) {
        $this->rentPriceStart = $rentPriceStart;
        return $this;
    }


    public function getRentPriceEnd() {
        return $this->rentPriceEnd;
    }


    public function setRentPriceEnd(int $rentPriceEnd = null) {
        $this->rentPriceEnd = $rentPriceEnd;
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

        if (!empty($this->rentPriceStart)) {
            $criteria->andWhere($criteria->expr()->gte("rentPrice", $this->rentPriceStart));
        }

        if (!empty($this->rentPriceEnd)) {
            $criteria->andWhere($criteria->expr()->lte("rentPrice", $this->rentPriceEnd));
        }

        if (!empty($this->types)) {
            $criteria->andWhere($criteria->expr()->in("type", $this->types));
        }

        if (!empty($this->startDateAfter)) {
            $criteria->andWhere($criteria->expr()->gte("startDate", $this->startDateAfter));
        }

        if (!empty($this->startDateBefore)) {
            $criteria->andWhere($criteria->expr()->lte("startDate", $this->startDateBefore));
        }

        if (!empty($this->endDateAfter)) {
            $criteria->andWhere($criteria->expr()->gte("endDate", $this->endDateAfter));
        }

        if (!empty($this->endDateBefore)) {
            $criteria->andWhere($criteria->expr()->lte("endDate", $this->endDateBefore));
        }

        return $criteria;
    }

}