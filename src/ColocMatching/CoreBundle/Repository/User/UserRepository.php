<?php

namespace ColocMatching\CoreBundle\Repository\User;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository
{
    public function findWithPagination(int $offset, int $limit, string $orderBy, string $sort) {
        $queryBuilder = $this->createQueryBuilder('u');

        $queryBuilder = $this->setPagination($queryBuilder, $offset, $limit);
        $queryBuilder = $this->setOrderBy($queryBuilder, "u.$orderBy", $sort);

        return $queryBuilder->getQuery()->getResult();
    }


    public function selectFieldsWithPagination(array $fields, int $offset, int $limit, string $orderBy, string $sort) {
    	$queryBuilder = $this->createQueryBuilder('u');
    	
    	$queryBuilder->select($this->getReturnedFields($fields));
        $queryBuilder = $this->setPagination($queryBuilder, $offset, $limit);
        $queryBuilder = $this->setOrderBy($queryBuilder, "u.$orderBy", $sort);
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    
    public function selectFieldsFromOne(int $id, array $fields) {
    	$queryBuilder = $this->createQueryBuilder('u');
    	 
    	$queryBuilder
    		->select($this->getReturnedFields($fields))
    		->where($queryBuilder->expr()->eq('u.id', $id));
    	
    	return $queryBuilder->getQuery()->getOneOrNullResult();
    }


    public function countAll() {
        $queryBuilder = $this->createQueryBuilder('u');

        $queryBuilder->select($queryBuilder->expr()->count('u'));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
    
    
    private function setPagination(QueryBuilder $queryBuilder, int $offset, int $limit) {
    	return $queryBuilder
	    	->setMaxResults($limit)
	    	->setFirstResult($offset)
    	;
    }
    
    
    private function setOrderBy(QueryBuilder $queryBuilder, string $orderBy, string $sort) {
    	return $queryBuilder->orderBy($orderBy, $sort);
    }
    
    
    private function getReturnedFields(array $fields) {
    	/** @var array */
    	$returnedFields = array();
    	
    	foreach ($fields as $field) {
    		$returnedFields[] = "u.$field";
    	}
    	
    	return $returnedFields;
    }

}
