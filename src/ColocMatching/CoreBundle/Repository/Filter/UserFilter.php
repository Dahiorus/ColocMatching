<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use Doctrine\Common\Collections\Criteria;

/**
 * User query filter class
 *
 * @author brondon.ung
 */
class UserFilter extends AbstractFilter {


    public function __toString(): string {
        return sprintf("UserFilterFilter[%s] []", parent::__toString());
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Repository\Filter\AbstractFilter::buildCriteria()
     */
    public function buildCriteria(): Criteria {
        /** @var Criteria */
        $criteria = Criteria::create();
        
        // TODO: Auto-generated method stub
        

        return $criteria;
    }

}