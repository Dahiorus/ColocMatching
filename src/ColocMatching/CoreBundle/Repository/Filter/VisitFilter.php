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
class VisitFilter implements Searchable
{
    /**
     * @var int
     *
     * @SWG\Property(description="The visitor identifier")
     */
    private $visitorId;

    /**
     * @var int
     *
     * @SWG\Property(description="The visited entity identifier")
     */
    private $visitedId;

    /**
     * @var string
     *
     * @SWG\Property(description="The visited entity class")
     */
    private $visitedClass;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="Visited at 'since' filter", format="date-time")
     */
    private $visitedAtSince;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="Visited at 'until' filter", format="date-time")
     */
    private $visitedAtUntil;


    public function getVisitorId()
    {
        return $this->visitorId;
    }


    public function setVisitorId(?int $visitorId)
    {
        $this->visitorId = $visitorId;

        return $this;
    }


    public function getVisitedId()
    {
        return $this->visitedId;
    }


    public function setVisitedId(?int $visitedId)
    {
        $this->visitedId = $visitedId;
    }


    public function getVisitedClass()
    {
        return $this->visitedClass;
    }


    public function setVisitedClass(string $visitedClass)
    {
        $this->visitedClass = $visitedClass;

        return $this;
    }


    public function getVisitedAtSince()
    {
        return $this->visitedAtSince;
    }


    public function setVisitedAtSince(\DateTime $visitedAtSince = null)
    {
        $this->visitedAtSince = $visitedAtSince;

        return $this;
    }


    public function getVisitedAtUntil()
    {
        return $this->visitedAtUntil;
    }


    public function setVisitedAtUntil(\DateTime $visitedAtUntil = null)
    {
        $this->visitedAtUntil = $visitedAtUntil;

        return $this;
    }


    public function buildCriteria() : Criteria
    {
        /** @var Criteria */
        $criteria = Criteria::create();

        if (!empty($this->visitedClass))
        {
            $criteria->andWhere(Criteria::expr()->eq("visitedClass", $this->visitedClass));

            if (!empty($this->visitedId))
            {
                $criteria->andWhere(Criteria::expr()->eq("visitedId", $this->visitedId));
            }
        }

        if (!empty($this->visitedAtSince))
        {
            $criteria->andWhere(Criteria::expr()->gte("createdAt", $this->visitedAtSince));
        }

        if (!empty($this->visitedAtUntil))
        {
            $criteria->andWhere(Criteria::expr()->lte("createdAt", $this->visitedAtUntil));
        }

        return $criteria;
    }

}
