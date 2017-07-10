<?php

namespace ColocMatching\CoreBundle\Manager;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;

/**
 * Manager interface
 *
 * @author Dahiorus
 */
interface ManagerInterface {


    /**
     * Gets all instances of an entity with pagination filter
     *
     * @param PageableFilter $filter The pagination filter
     * @param array $fields          The fields to return
     *
     * @return array<EntityInterface>
     */
    public function list(PageableFilter $filter, array $fields = null) : array;


    /**
     * Gets one instance of an entity by its ID
     *
     * @param int $id       The ID of the instance
     * @param array $fields The fields to return
     *
     * @return EntityInterface|array
     * @throws EntityNotFoundException
     */
    public function read(int $id, array $fields = null);


    /**
     * Counts all instances of an entity
     *
     * @return int
     */
    public function countAll() : int;

}
