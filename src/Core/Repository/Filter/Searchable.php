<?php

namespace App\Core\Repository\Filter;

use Doctrine\Common\Collections\Criteria;

/**
 * Interface to implement for searching criteria filter
 *
 * @author Dahiorus
 */
interface Searchable
{
    /**
     * Build a filtering criteria from the filter
     *
     * @return Criteria
     */
    public function buildCriteria() : Criteria;

}