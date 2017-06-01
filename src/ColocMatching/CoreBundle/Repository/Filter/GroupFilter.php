<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use Doctrine\Common\Collections\Criteria;

class GroupFilter extends PageableFilter implements Searchable {


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Repository\Filter\Searchable::buildCriteria()
     */
    public function buildCriteria(): Criteria {
        /** @var Criteria */
        $criteria = Criteria::create();

        return $criteria;
    }

}