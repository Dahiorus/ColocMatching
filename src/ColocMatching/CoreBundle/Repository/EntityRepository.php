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


    public function findByPage(AbstractFilter $filter): array {
        $queryBuilder = $this->createQueryBuilder("e");
        
        $this->setPagination($queryBuilder, $filter);
        $this->setOrderBy($queryBuilder, $filter);
        
        return $queryBuilder->getQuery()->getResult();
    }


    public function selectFieldsByPage(array $fields, AbstractFilter $filter): array {
        $queryBuilder = $this->createQueryBuilder("e");
        
        $queryBuilder->select($this->getReturnedFields("e", $fields));
        $this->setPagination($queryBuilder, $filter);
        $this->setOrderBy($queryBuilder, $filter);
        
        return $queryBuilder->getQuery()->getResult();
    }


    public function selectFieldsFromOne(int $id, array $fields) {
        $queryBuilder = $this->createQueryBuilder("e");
        
        $queryBuilder->select($this->getReturnedFields("e", $fields))->where($queryBuilder->expr()->eq("e.id", $id));
        
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }


    public function count(): int {
        $queryBuilder = $this->createQueryBuilder("e");
        
        $queryBuilder->select($queryBuilder->expr()->countDistinct("e"));
        
        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    protected function setPagination(QueryBuilder &$queryBuilder, AbstractFilter $filter) {
        $queryBuilder->setMaxResults($filter->getSize())->setFirstResult($filter->getOffset());
    }


    protected function setOrderBy(QueryBuilder &$queryBuilder, AbstractFilter $filter, string $alias = "e") {
        $queryBuilder->orderBy("$alias." . $filter->getSort(), $filter->getOrder());
    }


    protected function getReturnedFields(string $alias, array $fields): array {
        /** @var array */
        $returnedFields = array ();
        
        foreach ($fields as $field) {
            $returnedFields[] = "$alias.$field";
        }
        
        return $returnedFields;
    }

}