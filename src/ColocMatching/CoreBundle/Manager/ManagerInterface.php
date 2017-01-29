<?php

namespace ColocMatching\CoreBundle\Manager;

use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;

/**
 * Manager interface
 *
 * @author brondon.ung
 */
interface ManagerInterface {


    /**
     * Get all instances of a resource with pagination filter
     *
     * @param AbstractFilter $filter The pagination filter
     * @param array $fields THe fields to return
     * @return array
     */
    public function list(AbstractFilter $filter, array $fields = null): array;


    /**
     * Get one instance of a resource by its ID
     *
     * @param int $id The ID of the instance
     * @param array $fields The fields to return
     * @return object|null
     */
    public function read(int $id, array $fields = null);


    /**
     * Count all instances of a resource
     * @return int
     */
    public function countAll(): int;


    /**
     * Count instances corresponding to the filter
     * @param AbstractFilter $filter The filter
     * @return int
     */
    public function countBy(AbstractFilter $filter): int;

}
