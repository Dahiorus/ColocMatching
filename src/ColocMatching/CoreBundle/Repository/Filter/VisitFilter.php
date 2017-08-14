<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use Doctrine\Common\Collections\Criteria;
use Swagger\Annotations as SWG;

/**
 * Visit query filter class
 *
 * @SWG\Definition(definition="VisitFilter")
 *
 * @author Dahiorus
 */
class VisitFilter extends PageableFilter implements Searchable {

    /**
     * @var int
     *
     * @SWG\Property(description="The Id of the visitor")
     */
    private $visitorId;

    /**
     * @var int
     *
     * @SWG\Property(description="The Id of the visited entity")
     */
    private $visitedId;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="Visited at 'since' filter", format="datetime")
     */
    private $visitedAtSince;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="Visited at 'until' filter", format="datetime")
     */
    private $visitedAtUntil;


    /**
     * Constructor
     */
    public function __construct() {
    }


    public function getVisitorId() {
        return $this->visitorId;
    }


    public function setVisitorId(?int $visitorId) {
        $this->visitorId = $visitorId;

        return $this;
    }


    public function getVisitedId() {
        return $this->visitedId;
    }


    public function setVisitedId(?int $visitedId) {
        $this->visitedId = $visitedId;
    }


    public function getVisitedAtSince() {
        return $this->visitedAtSince;
    }


    public function setVisitedAtSince(\DateTime $visitedAtSince = null) {
        $this->visitedAtSince = $visitedAtSince;

        return $this;
    }


    public function getVisitedAtUntil() {
        return $this->visitedAtUntil;
    }


    public function setVisitedAtUntil(\DateTime $visitedAtUntil = null) {
        $this->visitedAtUntil = $visitedAtUntil;

        return $this;
    }


    public function buildCriteria() : Criteria {
        /** @var Criteria */
        $criteria = Criteria::create();

        if (!empty($this->visitedAtSince)) {
            $criteria->andWhere(Criteria::expr()->gte("visitedAt", $this->visitedAtSince));
        }

        if (!empty($this->visitedAtUntil)) {
            $criteria->andWhere(Criteria::expr()->lte("visitedAt", $this->visitedAtUntil));
        }

        return $criteria;
    }

}
