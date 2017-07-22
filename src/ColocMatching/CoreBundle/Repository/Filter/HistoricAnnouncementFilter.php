<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use Doctrine\Common\Collections\Criteria;
use Swagger\Annotations as SWG;

/**
 * Historic announcement query filter class
 *
 * @SWG\Definition(definition="HistoricAnnouncementFilter")
 *
 * @author Dahiorus
 */
class HistoricAnnouncementFilter extends AbstractAnnouncementFilter {

    /**
     * @var integer
     *
     * @SWG\Property(description="The Id of the creator")
     */
    private $creatorId;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="Date of creation 'since' filter", format="date")
     */
    private $createdAtSince;


    public function __toString() : string {
        $types = empty($this->types) ? "" : implode(", ", $this->types);
        $startDateAfter = empty($this->startDateAfter) ? "" : $this->startDateAfter->format(\DateTime::ISO8601);
        $startDateBefore = empty($this->startDateBefore) ? "" : $this->startDateBefore->format(\DateTime::ISO8601);
        $endDateAfter = empty($this->endDateAfter) ? "" : $this->endDateAfter->format(\DateTime::ISO8601);
        $endDateBefore = empty($this->endDateBefore) ? "" : $this->endDateBefore->format(\DateTime::ISO8601);
        $createdAtSince = empty($this->createdAtSince) ? "" : $this->createdAtSince->format(\DateTime::ISO8601);

        return sprintf(
            "HistoricAnnouncementFilter [%s, address: %s, rentPrice: [%d - %d], types: [%s], startDate: ['%s' - '%s'], endDate: ['%s' - '%s'], creatorId: %d, createdAtSince: '%s']",
            parent::__toString(), $this->address, $this->rentPriceStart, $this->rentPriceEnd, $types, $startDateAfter,
            $startDateBefore, $endDateAfter, $endDateBefore, $this->creatorId, $createdAtSince);
    }


    public function getCreatorId() {
        return $this->creatorId;
    }


    public function setCreatorId(?int $creatorId) {
        $this->creatorId = $creatorId;
    }


    public function getCreatedAtSince() {
        return $this->createdAtSince;
    }


    public function setCreatedAtSince(\DateTime $createdAtSince = null) {
        $this->createdAtSince = $createdAtSince;

        return $this;
    }


    public function buildCriteria() : Criteria {
        $criteria = parent::buildCriteria();

        if (!empty($this->createdAtSince)) {
            $criteria->andWhere($criteria->expr()->gte("createdAt", $this->createdAtSince));
        }

        return $criteria;
    }

}