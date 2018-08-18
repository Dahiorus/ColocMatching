<?php

namespace App\Core\Repository\Visit;

use App\Core\Entity\User\User;
use App\Core\Entity\Visit\Visit;
use App\Core\Repository\EntityRepository;
use App\Core\Repository\Filter\Pageable\Pageable;
use App\Core\Repository\Filter\VisitFilter;
use Doctrine\ORM\QueryBuilder;

class VisitRepository extends EntityRepository
{
    protected const ALIAS = "v";
    protected const VISITOR_ALIAS = "u";


    /**
     * Finds visits done by a user with paging
     *
     * @param User $visitor The visitor
     * @param Pageable $pageable [optional] Paging information
     *
     * @return Visit[]
     */
    public function findByVisitor(User $visitor, Pageable $pageable = null) : array
    {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->joinVisitorId($queryBuilder, $visitor->getId());

        if (!empty($pageable))
        {
            $this->setPaging($queryBuilder, $pageable);
        }

        $query = $queryBuilder->getQuery();
        $query->useQueryCache(true);

        return $query->getResult();
    }


    /**
     * Counts visits done by a user
     *
     * @param User $visitor The visitor
     *
     * @return int The visits count
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countByVisitor(User $visitor) : int
    {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));
        $this->joinVisitorId($queryBuilder, $visitor->getId());

        $query = $queryBuilder->getQuery();
        $query->useQueryCache(true);

        return $query->getSingleScalarResult();
    }


    /**
     * Creates a query builder with the filter
     *
     * @param VisitFilter $filter
     *
     * @return QueryBuilder
     * @throws \Doctrine\ORM\Query\QueryException
     */
    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->addCriteria($filter->buildCriteria());

        if (!empty($filter->getVisitorId()))
        {
            $this->joinVisitorId($queryBuilder, $filter->getVisitorId());
        }

        return $queryBuilder;
    }


    private function joinVisitorId(QueryBuilder $queryBuilder, int $visitorId)
    {
        $queryBuilder->join(self::ALIAS . ".visitor", self::VISITOR_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::VISITOR_ALIAS . ".id", ":visitorId"));
        $queryBuilder->setParameter("visitorId", $visitorId);
    }

}
