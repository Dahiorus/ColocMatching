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
     * @return array
	 */
    public function getAll(AbstractFilter $filter) : array;
    
    /**
     * Get one instance of a resource by its ID
     *
     * @param int $id The ID of the instance
     * @return object|null
     */
    public function getById(int $id);
    
    /**
     * Get the specified fields for the instances of a resource with pagination filter
     *
     * @param AbstractFilter $filter The pagination filter
     * @return array
     */
    public function getFields(array $fields, AbstractFilter $filter) : array;
    
    /**
     * Get the specified fields for one instance of a resource by its ID
     *
     * @param int $id The ID of the instance
     * @param array $fields The fields to return
     * @return object|null
     */
    public function getFieldsById(int $id, array $fields);
    
    
    /**
     * Count all instances of a resource
     * @return int
     */
    public function countAll() : int;
}
