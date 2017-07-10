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
class GroupFilter extends PageableFilter implements Searchable {


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Repository\Filter\Searchable::buildCriteria()
     */
    public function buildCriteria() : Criteria {
        /** @var Criteria */
        $criteria = Criteria::create();

        return $criteria;
    }

}