<?php

namespace Appartoo\CoreBundle\Manager;

/**
 * Manager interface
 * 
 * @author brondon.ung
 */
interface ManagerInterface {
    public function getAll();
    
    public function getWithPagination(int $page, int $maxResults);
    
    public function getById(int $id);
    
    public function getBy(array $criteria);
    
    public function countAll();
}
