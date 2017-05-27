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


    public function findByPageable(PageableFilter $filter, array $fields = null): array {
        $queryBuilder = $this->createQueryBuilder("e");

        $this->setPagination($queryBuilder, $filter);

        if (!empty($fields)) {
            $queryBuilder->select($this->getReturnedFields("e", $fields));
        }

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


    protected function setPagination(QueryBuilder &$queryBuilder, PageableFilter $filter) {
        $queryBuilder->setMaxResults($filter->getSize())->setFirstResult($filter->getOffset());
        $this->setOrderBy($queryBuilder, $filter);
    }


    protected function setOrderBy(QueryBuilder &$queryBuilder, PageableFilter $filter, string $alias = "e") {
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