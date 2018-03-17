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
class HistoricAnnouncementFilter extends AbstractAnnouncementFilter
{
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


    public function __toString() : string
    {
        $createdAtSince = empty($this->createdAtSince) ? "" : $this->createdAtSince->format(\DateTime::ISO8601);

        return parent::__toString() . "[creatorId = " . $this->creatorId . ", createdAtSince = " . $createdAtSince
            . "]";
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