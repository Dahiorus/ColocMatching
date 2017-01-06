<?php

namespace ColocMatching\CoreBundle\Manager;

/**
 * Manager interface
 *
 * @author brondon.ung
 */
interface ManagerInterface {
	/**
     * Get all instances of a resource with pagination
     *
     * @param int $page The page to get
     * @param int $maxResults The number of results to return
     * @param string $orderBy The name of the attrbute to order the results
     * @param string $sort 'asc' if ascending order, 'desc' if descending order
	 */
    public function getAll(int $page, int $maxResults, string $orderBy, string $sort);
    
    /**
     * Get one instance of a resource by its ID
     *
     * @param int $id The ID of the instance
     */
    public function getById(int $id);
    
    /**
     * Get the specified fields for the instances of a resource with pagination
     *
     * @param array $fields The fields to return
     * @param int $page The page to get
     * @param int $maxResults The number of results to return
     * @param string $orderBy The name of the attrbute to order the results
     * @param string $sort 'asc' if ascending order, 'desc' if descending order
     */
    public function getFields(array $fields, int $page, int $maxResults, string $orderBy, string $sort);
    
    /**
     * Get the specified fields for one instance of a resource by its ID
     *
     * @param int $id The ID of the instance
     * @param array $fields The fields to return
     */
    public function getFieldsById(int $id, array $fields);
    
    
    /**
     * Count all instances of a resource
     */
    public function countAll();
}
