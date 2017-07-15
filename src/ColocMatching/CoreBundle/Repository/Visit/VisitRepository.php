<?php

namespace ColocMatching\CoreBundle\Repository\Visit;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use Doctrine\ORM\QueryBuilder;

abstract class VisitRepository extends EntityRepository {

    protected const ALIAS = "v";
    protected const VISITOR_ALIAS = "u";
    protected const VISITED_ALIAS = "t";


    public function findByFilter(VisitFilter $filter, array $fields = null) : array {
        $queryBuilder = $this->createFilterQueryBuilder($filter);
        $this->setPagination($queryBuilder, $filter, self::ALIAS);

        if (!empty($fields)) {
            $queryBuilder->select($this->getReturnedFields(self::ALIAS, $fields));
        }

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByFilter(VisitFilter $filter) : int {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter);
        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    public function findByVisited(Visitable $visited, PageableFilter $filter) : array {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $this->setPagination($queryBuilder, $filter, self::ALIAS);

        $this->joinVisitedId($queryBuilder, $visited->getId());

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByVisited(Visitable $visited) : int {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->joinVisitedId($queryBuilder, $visited->getId());

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    public function findByVisitor(User $visitor, PageableFilter $filter) : array {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $this->setPagination($queryBuilder, $filter, self::ALIAS);

        $this->joinVisitorId($queryBuilder, $visitor->getId());

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByVisitor(User $visitor) : int {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->joinVisitorId($queryBuilder, $visitor->getId());

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    private function createFilterQueryBuilder(VisitFilter $filter) : QueryBuilder {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->addCriteria($filter->buildCriteria());

        if (!empty($filter->getVisitorId())) {
            $this->joinVisitorId($queryBuilder, $filter->getVisitorId());
        }

        if (!empty($filter->getVisitedId())) {
            $this->joinVisitedId($queryBuilder, $filter->getVisitedId());
        }

        return $queryBuilder;
    }


    private function joinVisitorId(QueryBuilder &$queryBuilder, int $visitorId) {
        $queryBuilder->join(self::ALIAS . ".visitor", self::VISITOR_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::VISITOR_ALIAS . ".id", ":visitorId"));
        $queryBuilder->setParameter("visitorId", $visitorId);
    }


    private function joinVisitedId(QueryBuilder $queryBuilder, int $visitedId) {
        $queryBuilder->join(self::ALIAS . ".visited", self::VISITED_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::VISITED_ALIAS . ".id", ":visitedId"));
        $queryBuilder->setParameter("visitedId", $visitedId);
    }

}
