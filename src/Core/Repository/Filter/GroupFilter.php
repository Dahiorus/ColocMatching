<?php

namespace App\Core\Repository\Filter;

use Doctrine\Common\Collections\Criteria;

/**
 * Announcement query filter class
 *
 * @author Dahiorus
 */
class GroupFilter extends AbstractPageableFilter implements Searchable
{
    /**
     * @var boolean
     */
    private $withDescription = false;

    /**
     * @var integer
     */
    private $budgetMin;

    /**
     * @var integer
     */
    private $budgetMax;

    /**
     * @var string
     */
    private $status;

    /**
     * @var integer
     */
    private $countMembers;

    /**
     * @var boolean
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
     * @see \App\Core\Repository\Filter\Searchable::buildCriteria()
     */
    public function buildCriteria() : Criteria
    {
        /** @var Criteria */
        $criteria = Criteria::create();

        if ($this->withDescription)
        {
            $criteria->andWhere(Criteria::expr()->neq("description", null));
        }

        if (!is_null($this->budgetMin))
        {
            $criteria->andWhere(Criteria::expr()->gte("budget", $this->budgetMin));
        }

        if (!is_null($this->budgetMax))
        {
            $criteria->andWhere(Criteria::expr()->lte("budget", $this->budgetMax));
        }

        if (!empty($this->status))
        {
            $criteria->andWhere(Criteria::expr()->eq("status", $this->status));
        }

        return $criteria;
    }

}