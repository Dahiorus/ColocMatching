<?php

namespace ColocMatching\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository as BaseRepository;
use Doctrine\ORM\QueryBuilder;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;

/**
 * Abstract repository
 *
 * @author brondon.ung
 */
abstract class EntityRepository extends BaseRepository {
	
	public function findByPage(AbstractFilter $filter) : array {
		$queryBuilder = $this->createQueryBuilder("e");
	
		$queryBuilder = $this->setPagination($queryBuilder, $filter->getOffset(), $filter->getSize());
		$queryBuilder = $this->setOrderBy($queryBuilder, "e.".$filter->getOrderBy(), $filter->getSort());
	
		return $queryBuilder->getQuery()->getResult();
	}
	
	
	public function selectFieldsByFilter(array $fields, AbstractFilter $filter) : array {
		$queryBuilder = $this->createQueryBuilder("e");
		 
		$queryBuilder->select($this->getReturnedFields("e", $fields));
		$queryBuilder = $this->setPagination($queryBuilder, $filter->getOffset(), $filter->getSize());
		$queryBuilder = $this->setOrderBy($queryBuilder, "e.".$filter->getOrderBy(), $filter->getSort());
	
		return $queryBuilder->getQuery()->getResult();
	}
	
	
	public function selectFieldsFromOne(int $id, array $fields) {
		$queryBuilder = $this->createQueryBuilder("e");
	
		$queryBuilder
			->select($this->getReturnedFields("e", $fields))
			->where($queryBuilder->expr()->eq("e.id", $id));
		 
		return $queryBuilder->getQuery()->getOneOrNullResult();
	}
	
	
	public function count(): int {
		$queryBuilder = $this->createQueryBuilder("e");
	
		$queryBuilder->select($queryBuilder->expr()->count("e"));
	
		return $queryBuilder->getQuery()->getSingleScalarResult();
	}
	
	
	protected function setPagination(QueryBuilder $queryBuilder, int $offset, int $limit) : QueryBuilder {
		return $queryBuilder
			->setMaxResults($limit)
			->setFirstResult($offset);
	}
	
	
	protected function setOrderBy(QueryBuilder $queryBuilder, string $orderBy, string $sort) : QueryBuilder {
		return $queryBuilder->orderBy($orderBy, $sort);
	}
	
	
	protected function getReturnedFields(string $alias, array $fields) : array {
		/** @var array */
		$returnedFields = array();
		 
		foreach ($fields as $field) {
			$returnedFields[] = "$alias.$field";
		}
		 
		return $returnedFields;
	}
	
}