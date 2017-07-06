<?php

namespace ColocMatching\CoreBundle\Repository\Group;

use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use Doctrine\ORM\QueryBuilder;

class GroupRepository extends EntityRepository {

    protected const ALIAS = "g";


    public function findByFilter(GroupFilter $filter, array $fields = null): array {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter);
        $this->setPagination($queryBuilder, $filter, self::ALIAS);

        if (!empty($fields)) {
            $queryBuilder->select($this->getReturnedFields(self::ALIAS, $fields));
        }

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByFilter(GroupFilter $filter): int {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter);
        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    private function createFilterQueryBuilder(GroupFilter $filter) : QueryBuilder {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->addCriteria($filter->buildCriteria());

        return $queryBuilder;
    }

}