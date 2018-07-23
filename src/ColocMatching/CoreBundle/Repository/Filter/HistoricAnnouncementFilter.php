<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use Doctrine\Common\Collections\Criteria;

/**
 * Historic announcement query filter class
 *
 * @author Dahiorus
 */
class HistoricAnnouncementFilter extends AbstractAnnouncementFilter
{
    /**
     * @var integer
     */
    private $creatorId;

    /**
     * @var \DateTime
     */
    private $createdAtSince;


    public function __toString() : string
    {
        $createdAtSince = empty($this->createdAtSince) ? "" : $this->createdAtSince->format(\DateTime::ISO8601);

        return parent::__toString() . "[creatorId = " . $this->creatorId . ", createdAtSince = " . $createdAtSince . "]";
    }


    public function getCreatorId()
    {
        return $this->creatorId;
    }


    public function setCreatorId(?int $creatorId)
    {
        $this->creatorId = $creatorId;
    }


    public function getCreatedAtSince()
    {
        return $this->createdAtSince;
    }


    public function setCreatedAtSince(\DateTime $createdAtSince = null)
    {
        $this->createdAtSince = $createdAtSince;

        return $this;
    }


    public function buildCriteria() : Criteria
    {
        $criteria = parent::buildCriteria();

        if (!empty($this->createdAtSince))
        {
            $criteria->andWhere($criteria->expr()->gte("creationDate", $this->createdAtSince));
        }

        return $criteria;
    }

}