<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use Doctrine\Common\Collections\Criteria;
use Swagger\Annotations as SWG;

abstract class AbstractAnnouncementFilter extends PageableFilter implements Searchable
{
    /**
     * @var Address
     *
     * @SWG\Property(type="string", description="Location filter")
     */
    protected $address;

    /**
     * @var integer
     *
     * @SWG\Property(description="Rent price start range filter")
     */
    protected $rentPriceStart;

    /**
     * @var integer
     *
     * @SWG\Property(description="Rent price end range filter")
     */
    protected $rentPriceEnd;

    /**
     * @var array
     *
     * @SWG\Property(description="Types filter", @SWG\Items(type="string"))
     */
    protected $types = array ();

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="Start date 'from' filter", format="date")
     */
    protected $startDateAfter;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="Start date 'to' filter", format="date")
     */
    protected $startDateBefore;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="End date 'from' filter", format="date")
     */
    protected $endDateAfter;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="End date 'to' filter", format="date")
     */
    protected $endDateBefore;


    public function __toString() : string
    {
        $types = empty($this->types) ? null : implode(", ", $this->types);
        $startDateAfter = empty($this->startDateAfter) ? null : $this->startDateAfter->format(\DateTime::ISO8601);
        $startDateBefore = empty($this->startDateBefore) ? null : $this->startDateBefore->format(\DateTime::ISO8601);
        $endDateAfter = empty($this->endDateAfter) ? null : $this->endDateAfter->format(\DateTime::ISO8601);
        $endDateBefore = empty($this->endDateBefore) ? null : $this->endDateBefore->format(\DateTime::ISO8601);

        return parent::__toString() . "[address = '" . $this->address . "', rentPriceStart = " . $this->rentPriceStart
            . ", rentPriceEnd = " . $this->rentPriceEnd . ", types = " . $types
            . ", startDateAfter = " . $startDateAfter . ", startDateBefore = " . $startDateBefore
            . ", endDateAfter = " . $endDateAfter . ", endDateBefore = " . $endDateBefore . "]";
    }


    public function getAddress()
    {
        return $this->address;
    }


    public function setAddress(Address $address = null)
    {
        $this->address = $address;

        return $this;
    }


    public function getRentPriceStart()
    {
        return $this->rentPriceStart;
    }


    public function setRentPriceStart(?int $rentPriceStart)
    {
        $this->rentPriceStart = $rentPriceStart;

        return $this;
    }


    public function getRentPriceEnd()
    {
        return $this->rentPriceEnd;
    }


    public function setRentPriceEnd(?int $rentPriceEnd)
    {
        $this->rentPriceEnd = $rentPriceEnd;

        return $this;
    }


    public function getTypes()
    {
        return $this->types;
    }


    public function setTypes(array $types = null)
    {
        $this->types = $types;

        return $this;
    }


    public function getStartDateAfter()
    {
        return $this->startDateAfter;
    }


    public function setStartDateAfter(\DateTime $startDateAfter = null)
    {
        $this->startDateAfter = $startDateAfter;

        return $this;
    }


    public function getStartDateBefore()
    {
        return $this->startDateBefore;
    }


    public function setStartDateBefore(\DateTime $startDateBefore = null)
    {
        $this->startDateBefore = $startDateBefore;

        return $this;
    }


    public function getEndDateAfter()
    {
        return $this->endDateAfter;
    }


    public function setEndDateAfter(\DateTime $endDateAfter = null)
    {
        $this->endDateAfter = $endDateAfter;

        return $this;
    }


    public function getEndDateBefore()
    {
        return $this->endDateBefore;
    }


    public function setEndDateBefore(\DateTime $endDateBefore = null)
    {
        $this->endDateBefore = $endDateBefore;

        return $this;
    }


    public function buildCriteria() : Criteria
    {
        $criteria = Criteria::create();

        if (!is_null($this->rentPriceStart))
        {
            $criteria->andWhere($criteria->expr()->gte("rentPrice", $this->rentPriceStart));
        }

        if (!is_null($this->rentPriceEnd))
        {
            $criteria->andWhere($criteria->expr()->lte("rentPrice", $this->rentPriceEnd));
        }

        if (!empty($this->types))
        {
            $criteria->andWhere($criteria->expr()->in("type", $this->types));
        }

        if (!empty($this->startDateAfter))
        {
            $criteria->andWhere($criteria->expr()->gte("startDate", $this->startDateAfter));
        }

        if (!empty($this->startDateBefore))
        {
            $criteria->andWhere($criteria->expr()->lte("startDate", $this->startDateBefore));
        }

        if (!empty($this->endDateAfter))
        {
            $criteria->andWhere($criteria->expr()->gte("endDate", $this->endDateAfter));
        }

        if (!empty($this->endDateBefore))
        {
            $criteria->andWhere($criteria->expr()->lte("endDate", $this->endDateBefore));
        }

        if (!empty($this->address))
        {
            $this->buildAddressCriteria($criteria, $this->address);
        }

        return $criteria;
    }


    private function buildAddressCriteria(Criteria $criteria, Address $address)
    {
        if (!empty($address->getStreetNumber()))
        {
            $criteria->andWhere($criteria->expr()->eq("location.streetNumber", $address->getStreetNumber()));
        }
        if (!empty($address->getRoute()))
        {
            $criteria->andWhere($criteria->expr()->eq("location.route", $address->getRoute()));
        }
        if (!empty($address->getLocality()))
        {
            $criteria->andWhere($criteria->expr()->eq("location.locality", $address->getLocality()));
        }
        if (!empty($address->getCountry()))
        {
            $criteria->andWhere($criteria->expr()->eq("location.country", $address->getCountry()));
        }
        if (!empty($address->getZipCode()))
        {
            $criteria->andWhere($criteria->expr()->eq("location.zipCode", $address->getZipCode()));
        }
    }
}
