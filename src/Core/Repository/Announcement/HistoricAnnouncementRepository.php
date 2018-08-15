<?php

namespace App\Core\Repository\Announcement;

use App\Core\Repository\EntityRepository;
use App\Core\Repository\Filter\HistoricAnnouncementFilter;
use Doctrine\ORM\QueryBuilder;

class HistoricAnnouncementRepository extends EntityRepository
{
    protected const ALIAS = "ha";
    private const CREATOR_ALIAS = "c";


    /**
     * Creates a query builder with the filter
     *
     * @param HistoricAnnouncementFilter $filter
     *
     * @return QueryBuilder
     * @throws \Doctrine\ORM\Query\QueryException
     */
    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->addCriteria($filter->buildCriteria());

        if (!empty($filter->getCreatorId()))
        {
            $this->joinCreatorId($queryBuilder, $filter->getCreatorId());
        }

        return $queryBuilder;
    }


    private function joinCreatorId(QueryBuilder $queryBuilder, int $creatorId)
    {
        $queryBuilder->join(self::ALIAS . ".creator", self::CREATOR_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::CREATOR_ALIAS . ".id", ":creatorId"));
        $queryBuilder->setParameter("creatorId", $creatorId);
    }

}