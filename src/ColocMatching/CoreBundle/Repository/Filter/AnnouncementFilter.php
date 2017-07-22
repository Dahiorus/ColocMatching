<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use Doctrine\Common\Collections\Criteria;
use Swagger\Annotations as SWG;

/**
 * Announcement query filter class
 *
 * @SWG\Definition(definition="AnnouncementFilter")
 *
 * @author brondon.ung
 */
class AnnouncementFilter extends AbstractAnnouncementFilter {

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


    public function __toString() : string {
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
            $this->withPictures . ", createdAtSince=" . $createdAtSince . "]";
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
    public function buildCriteria() : Criteria {
        /** @var Criteria */
        $criteria = parent::buildCriteria();

        if (!empty($this->createdAtSince)) {
            $criteria->andWhere($criteria->expr()->gte("createdAt", $this->createdAtSince));
        }

        return $criteria;
    }

}