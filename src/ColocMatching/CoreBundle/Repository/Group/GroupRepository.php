<?php

namespace ColocMatching\CoreBundle\Repository\Group;

use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use Doctrine\ORM\QueryBuilder;

class GroupRepository extends EntityRepository {


    public function findByFilter(GroupFilter $filter, array $fields = null): array {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter, "g");
        $this->setPagination($queryBuilder, $filter, "g");

        if (!empty($fields)) {
            $queryBuilder->select($this->getReturnedFields("g", $fields));
        }

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByFilter(GroupFilter $filter): int {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter, "g");
        $queryBuilder->select($queryBuilder->expr()->countDistinct("g"));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    private function createFilterQueryBuilder(GroupFilter $filter, string $alias = "g"): QueryBuilder {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder($alias);
        $queryBuilder->addCriteria($filter->buildCriteria());

        return $queryBuilder;
    }

}