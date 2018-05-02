<?php

namespace ColocMatching\CoreBundle\Repository\Filter\Pageable;

/**
 * Paging and sorting filter for queries
 *
 * @author Dahiorus
 */
interface Pageable
{
    /**
     * Gets the page number (from 1)
     * @return int
     */
    public function getPage() : int;


    /**
     * Gets the page size
     * @return int
     */
    public function getSize() : int;


    /**
     * Get the page offset
     * @return int
     */
    public function getOffset() : int;


    /**
     * Gets the page sorting (property => order)
     *
     * @return Sort[]
     */
    public function getSorts() : array;
}