<?php

namespace ColocMatching\CoreBundle\Repository\Announcement;

use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\HistoricAnnouncementFilter;
use Doctrine\ORM\QueryBuilder;

class HistoricAnnouncementRepository extends EntityRepository {

    protected const ALIAS = "ha";
    private const CREATOR_ALIAS = "c";


    public function findByFilter(HistoricAnnouncementFilter $filter, array $fields = null) : array {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter);
        $this->setPagination($queryBuilder, $filter, self::ALIAS);

        if (!empty($fields)) {
            $queryBuilder->select($this->getReturnedFields(self::ALIAS, $fields));
        }

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByFilter(HistoricAnnouncementFilter $filter) : int {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter);
        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    private function createFilterQueryBuilder(HistoricAnnouncementFilter $filter) : QueryBuilder {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->addCriteria($filter->buildCriteria());

        if (!empty($filter->getCreatorId())) {
            $this->joinCreatorId($queryBuilder, $filter->getCreatorId());
        }

        return $queryBuilder;
    }


    private function joinCreatorId(QueryBuilder &$queryBuilder, int $creatorId) {
        $queryBuilder->join(self::ALIAS . ".creator", self::CREATOR_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::CREATOR_ALIAS . ".id", ":creatorId"));
        $queryBuilder->setParameter("creatorId", $creatorId);
    }

}