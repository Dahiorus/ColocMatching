<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use Doctrine\Common\Collections\Criteria;
use Swagger\Annotations as SWG;

/**
 * Announcement query filter class
 *
 * @SWG\Definition(definition="GroupFilter")
 *
 * @author Dahiorus
 */
class GroupFilter implements Searchable
{
    /**
     * @var boolean
     *
     * @SWG\Property(description="Only groups with a description", default=false)
     */
    private $withDescription = false;

    /**
     * @var integer
     *
     * @SWG\Property(description="Budget 'min' filter")
     */
    private $budgetMin;

    /**
     * @var integer
     *
     * @SWG\Property(description="Budget 'max' filter")
     */
    private $budgetMax;

    /**
     * @var string
     *
     * @SWG\Property(description="Group status")
     */
    private $status;

    /**
     * @var integer
     *
     * @SWG\Property(description="Minimal count members filter")
     */
    private $countMembers;

    /**
     * @var boolean
     *
     * @SWG\Property(description="Only groups with a picture", default=false)
     */
    private $withPicture = false;


    public function __toString() : string
    {
        return get_class($this) . " [withDescription=" . $this->withDescription . ", budgetMin=" . $this->budgetMin
            . ", budgetMax=" . $this->budgetMax . ", status=" . $this->status . ", countMembers=" . $this->countMembers
            . ", withPicture=" . $this->withPicture . "]";
    }


    public function withDescription()
    {
        return $this->withDescription;
    }


    public function setWithDescription(bool $withDescription)
    {
        $this->withDescription = $withDescription;

        return $this;
    }


    public function getBudgetMin()
    {
        return $this->budgetMin;
    }


    public function setBudgetMin(?int $budgetMin)
    {
        $this->budgetMin = $budgetMin;

        return $this;
    }


    public function getBudgetMax()
    {
        return $this->budgetMax;
    }


    public function setBudgetMax(?int $budgetMax)
    {
        $this->budgetMax = $budgetMax;

        return $this;
    }


    public function getStatus()
    {
        return $this->status;
    }


    public function setStatus(?string $status)
    {
        $this->status = $status;

        return $this;
    }


    public function getCountMembers()
    {
        return $this->countMembers;
    }


    public function setCountMembers(?int $countMembers)
    {
        $this->countMembers = $countMembers;

        return $this;
    }


    public function withPicture()
    {
        return $this->withPicture;
    }


    public function setWithPicture(bool $withPicture)
    {
        $this->withPicture = $withPicture;

        return $this;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Repository\Filter\Searchable::buildCriteria()
     */
    public function buildCriteria() : Criteria
    {
        /** @var Criteria */
        $criteria = Criteria::create();

        if ($this->withDescription)
        {
            $criteria->andWhere($criteria->expr()->neq("description", null));
        }

        if (!is_null($this->budgetMin))
        {
            $criteria->andWhere($criteria->expr()->gte("budget", $this->budgetMin));
        }

        if (!is_null($this->budgetMax))
        {
            $criteria->andWhere($criteria->expr()->lte("budget", $this->budgetMax));
        }

        if (!empty($this->status))
        {
            $criteria->andWhere($criteria->expr()->eq("status", $this->status));
        }

        return $criteria;
    }

}