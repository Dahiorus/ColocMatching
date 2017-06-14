<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use Doctrine\Common\Collections\Criteria;
use Swagger\Annotations as SWG;

/**
 * Announcement query filter class
 *
 * @SWG\Definition(definition="AnnouncementFilter")
 *
 * @author brondon.ung
 */
class AnnouncementFilter extends PageableFilter implements Searchable {

    /**
     * @var Address
     *
     * @SWG\Property(type="string", description="Location filter")
     */
    private $address;

    /**
     * @var integer
     *
     * @SWG\Property(description="Rent price start range filter")
     */
    private $rentPriceStart;

    /**
     * @var integer
     *
     * @SWG\Property(description="Rent price end range filter")
     */
    private $rentPriceEnd;

    /**
     * @var array
     *
     * @SWG\Property(description="Announcement types filter", @SWG\Items(type="string"))
     */
    private $types = array ();

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
     * @SWG\Property(description="Announcement status")
     */
    private $status;

    /**
     * @var boolean
     *
     * @SWG\Property(description="Only announcements with pictures")
     */
    private $withPictures = false;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="Date of creation 'since' filter", format="date")
     */
    private $createdAtSince;

    /**
     * @var HousingFilter
     *
     * @SWG\Property(ref="#/definitions/HousingFilter", description="Housing filter")
     */
    private $housingFilter;


    /**
     * Constructor
     */
    public function __construct() {
    }


    public function __toString(): string {
        $types = empty($this->types) ? null : implode(", ", $this->types);
        $startDateAfter = empty($this->startDateAfter) ? null : $this->startDateAfter->format(\DateTime::ISO8601);
        $startDateBefore = empty($this->startDateBefore) ? null : $this->startDateBefore->format(\DateTime::ISO8601);
        $endDateAfter = empty($this->endDateAfter) ? null : $this->endDateAfter->format(\DateTime::ISO8601);
        $endDateBefore = empty($this->endDateBefore) ? null : $this->endDateBefore->format(\DateTime::ISO8601);
        $createdAtSince = empty($this->createdAtSince) ? null : $this->createdAtSince->format(\DateTime::ISO8601);

        return "AnnouncementFilter [" . parent::__toString() . ", address=" . $this->address . ", rentPriceStart=" .
             $this->rentPriceStart . ", rentPriceEnd=" . $this->rentPriceEnd . ", types=(" . $types .
             "), startDateAfter=" . $startDateAfter . "startDateBefore=" . $startDateBefore . ", endDateAfter=" .
             $endDateAfter . ", endDateBefore=" . $endDateBefore . ", status='" . $this->status . ", withPictures=" .
             $this->withPictures . ", createAtSince=" . $createdAtSince . "]";
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


    public function setRentPriceStart(?int $rentPriceStart) {
        $this->rentPriceStart = $rentPriceStart;
        return $this;
    }


    public function getRentPriceEnd() {
        return $this->rentPriceEnd;
    }


    public function setRentPriceEnd(?int $rentPriceEnd) {
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


    public function getStatus() {
        return $this->status;
    }


    public function setStatus(?string $status) {
        $this->status = $status;
        return $this;
    }


    public function withPictures() {
        return $this->withPictures;
    }


    public function setWithPictures(?bool $withPictures) {
        $this->withPictures = $withPictures;
        return $this;
    }


    public function getCreatedAtSince() {
        return $this->createdAtSince;
    }


    public function setCreatedAtSince(\DateTime $createdAtSince = null) {
        $this->createdAtSince = $createdAtSince;
        return $this;
    }


    public function getHousingFilter() {
        return $this->housingFilter;
    }


    public function setHousingFilter(HousingFilter $housingFilter = null) {
        $this->housingFilter = $housingFilter;
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

        if (!empty($this->status)) {
            $criteria->andWhere($criteria->expr()->eq("status", $this->status));
        }

        if (!empty($this->createdAtSince)) {
            $criteria->andWhere($criteria->expr()->gte("createdAt", $this->createdAtSince));
        }

        return $criteria;
    }

}