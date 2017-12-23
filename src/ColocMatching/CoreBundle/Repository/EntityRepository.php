<?php

namespace ColocMatching\CoreBundle\Repository;

use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Doctrine\ORM\EntityRepository as BaseRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Abstract repository
 *
 * @author brondon.ung
 */
abstract class EntityRepository extends BaseRepository {

    protected const ALIAS = "e";


    public function findByPageable(PageableFilter $filter, array $fields = null) : array {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->setPagination($queryBuilder, $filter, self::ALIAS);

        if (!empty($fields)) {
            $queryBuilder->select($this->getReturnedFields(self::ALIAS, $fields));
        }

        return $queryBuilder->getQuery()->getResult();
    }


    public function findById(int $id, array $fields = null) {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $queryBuilder->where($queryBuilder->expr()->eq(self::ALIAS . ".id", ":id"));
        $queryBuilder->setParameter("id", $id);

        if (!empty($fields)) {
            $queryBuilder->select($this->getReturnedFields(self::ALIAS, $fields));
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }


    public function countAll() : int {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    protected function setPagination(QueryBuilder &$queryBuilder, PageableFilter $filter, string $alias) {
        $queryBuilder->setMaxResults($filter->getSize())->setFirstResult($filter->getOffset());
        $this->setOrderBy($queryBuilder, $filter, $alias);
    }


    protected function setOrderBy(QueryBuilder &$queryBuilder, PageableFilter $filter, string $alias) {
        $queryBuilder->orderBy("$alias." . $filter->getSort(), $filter->getOrder());
    }


    protected function getReturnedFields(string $alias, array $fields) : array {
        /** @var array */
        $returnedFields = array ();

        foreach ($fields as $field) {
            $returnedFields[] = "$alias.$field";
        }

        return $returnedFields;
    }

}
