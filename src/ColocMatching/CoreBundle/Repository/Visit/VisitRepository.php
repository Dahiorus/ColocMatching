<?php

namespace ColocMatching\CoreBundle\Repository\Visit;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visit;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use Doctrine\ORM\QueryBuilder;

class VisitRepository extends EntityRepository
{
    protected const ALIAS = "v";
    protected const VISITOR_ALIAS = "u";
    protected const VISITED_ALIAS = "t";


    /**
     * Finds visits done on a visited entity
     *
     * @param Visitable $visited The visited entity
     * @param PageableFilter $filter Paging information
     *
     * @return Visit[]
     */
    public function findByVisited(Visitable $visited, PageableFilter $filter) : array
    {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->setPaging($queryBuilder, $filter);
        $this->joinVisitedId($queryBuilder, $visited->getId());

        return $queryBuilder->getQuery()->getResult();
    }


    /**
     * Counts visits done on a visited entity
     *
     * @param Visitable $visited The visited entity
     *
     * @return int The visits count
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countByVisited(Visitable $visited) : int
    {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));
        $this->joinVisitedId($queryBuilder, $visited->getId());

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    /**
     * Finds visits done by a user with paging
     *
     * @param User $visitor The visitor
     * @param PageableFilter $filter Paging information
     *
     * @return Visit[]
     */
    public function findByVisitor(User $visitor, PageableFilter $filter) : array
    {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->setPaging($queryBuilder, $filter);
        $this->joinVisitorId($queryBuilder, $visitor->getId());

        return $queryBuilder->getQuery()->getResult();
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

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    /**
     * Deletes all visits done on a visitable
     *
     * @param Visitable $visitable The visitable
     *
     * @return int The number of deleted visits
     */
    public function deleteAllOfVisited(Visitable $visitable) : int
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $queryBuilder->delete();
        $queryBuilder->where($queryBuilder->expr()->eq(self::ALIAS . ".visited", ":visited"));
        $queryBuilder->setParameter("visited", $visitable);

        return $queryBuilder->getQuery()->execute();
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

        if (!empty($filter->getVisitedId()))
        {
            $this->joinVisitedId($queryBuilder, $filter->getVisitedId());
        }

        return $queryBuilder;
    }


    private function joinVisitorId(QueryBuilder $queryBuilder, int $visitorId)
    {
        $queryBuilder->join(self::ALIAS . ".visitor", self::VISITOR_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::VISITOR_ALIAS . ".id", ":visitorId"));
        $queryBuilder->setParameter("visitorId", $visitorId);
    }


    private function joinVisitedId(QueryBuilder $queryBuilder, int $visitedId)
    {
        $queryBuilder->join(self::ALIAS . ".visited", self::VISITED_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::VISITED_ALIAS . ".id", ":visitedId"));
        $queryBuilder->setParameter("visitedId", $visitedId);
    }

}
