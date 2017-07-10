<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\Collections\Criteria;

/**
 * Visit query filter class
 *
 * @author Dahiorus
 */
class VisitFilter extends PageableFilter implements Searchable {

    /**
     * @var User
     *
     */
    private $visitor;

    /**
     * @var \DateTime
     *
     */
    private $visitedAtSince;

    /**
     * @var \DateTime
     *
     */
    private $visitedAtUntil;


    /**
     * Constructor
     */
    public function __construct() {
    }


    public function getVisitor() {
        return $this->visitor;
    }


    public function setVisitor(User $visitor = null) {
        $this->visitor = $visitor;

        return $this;
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
            $criteria->andWhere($criteria->expr()->gte("visitedAt", $this->visitedAtSince));
        }

        if (!empty($this->visitedAtUntil)) {
            $criteria->andWhere($criteria->expr()->lte("visitedAt", $this->visitedAtUntil));
        }

        return $criteria;
    }

}
