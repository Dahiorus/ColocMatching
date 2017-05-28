<?php

namespace ColocMatching\CoreBundle\Repository\Announcement;

use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\HistoricAnnouncementFilter;
use Doctrine\ORM\QueryBuilder;

class HistoricAnnouncementRepository extends EntityRepository {


    public function findByFilter(HistoricAnnouncementFilter $filter, array $fields = null): array {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter, "ha");
        $this->setPagination($queryBuilder, $filter, "ha");

        if (!empty($fields)) {
            $queryBuilder->select($this->getReturnedFields("ha", $fields));
        }

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByFilter(HistoricAnnouncementFilter $filter): int {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter, "ha");
        $queryBuilder->select($queryBuilder->expr()->countDistinct("ha"));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    private function createFilterQueryBuilder(HistoricAnnouncementFilter $filter, string $alias = "ha"): QueryBuilder {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder($alias);
        $queryBuilder->addCriteria($filter->buildCriteria());

        return $queryBuilder;
    }

}