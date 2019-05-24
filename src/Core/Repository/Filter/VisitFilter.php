<?php

namespace App\Core\Repository\Filter;

use DateTime;
use Doctrine\Common\Collections\Criteria;
use JMS\Serializer\Annotation as Serializer;

/**
 * Visit query filter class
 *
 * @author Dahiorus
 */
class VisitFilter implements Searchable
{
    /**
     * @var int
     * @Serializer\Type("int")
     */
    private $visitorId;

    /**
     * @var int
     * @Serializer\Type("int")
     */
    private $visitedId;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $visitedClass;

    /**
     * @var DateTime
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:s'>")
     */
    private $visitedAtSince;

    /**
     * @var DateTime
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:s'>")
     */
    private $visitedAtUntil;


    public function __toString()
    {
        $visitedAtSince = empty($visitedAtSince) ? null : $this->visitedAtSince->format(DateTime::ISO8601);
        $visitedAtUntil = empty($visitedAtUntil) ? null : $this->visitedAtUntil->format(DateTime::ISO8601);

        return "VisitFilter [visitorId = " . $this->visitorId . ", visitedClass = " . $this->visitedClass
            . ", visitedId = " . $this->visitedId . ", visitedAtSince = " . $visitedAtSince
            . ", visitedAtUntil = " . $visitedAtUntil . "]";
    }


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


    public function setVisitedAtSince(DateTime $visitedAtSince = null)
    {
        $this->visitedAtSince = $visitedAtSince;

        return $this;
    }


    public function getVisitedAtUntil()
    {
        return $this->visitedAtUntil;
    }


    public function setVisitedAtUntil(DateTime $visitedAtUntil = null)
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
