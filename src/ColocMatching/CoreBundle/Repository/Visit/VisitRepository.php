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

        $this->joinVisited($queryBuilder, $visited);

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByVisited(Visitable $visited) : int {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->joinVisited($queryBuilder, $visited);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    public function findByVisitor(User $visitor, PageableFilter $filter) : array {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $this->setPagination($queryBuilder, $filter, self::ALIAS);

        $this->joinVisitor($queryBuilder, $visitor);

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByVisitor(User $visitor) : int {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->joinVisitor($queryBuilder, $visitor);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    private function createFilterQueryBuilder(VisitFilter $filter) : QueryBuilder {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->addCriteria($filter->buildCriteria());

        if (!empty($filter->getVisitor())) {
            $this->joinVisitor($queryBuilder, $filter->getVisitor());
        }

        return $queryBuilder;
    }


    private function joinVisitor(QueryBuilder &$queryBuilder, User $visitor) {
        $queryBuilder->join(self::ALIAS . ".visitor", self::VISITOR_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::VISITOR_ALIAS, ":visitor"));
        $queryBuilder->setParameter("visitor", $visitor);
    }


    private function joinVisited(QueryBuilder $queryBuilder, Visitable $visited) {
        $queryBuilder->join(self::ALIAS . ".visited", self::VISITED_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::VISITED_ALIAS, ":visited"));
        $queryBuilder->setParameter("visited", $visited);
    }

}
