<?php

namespace ColocMatching\CoreBundle\Repository\Visit;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use Doctrine\ORM\QueryBuilder;

class VisitRepository extends EntityRepository {

    private const VISIT_ALIAS = "v";
    private const VISITOR_ALIAS = "u";


    public function findByFilter(VisitFilter $filter, array $fields = null) : array {
        return array ();
    }


    public function countByFilter(VisitFilter $filter) : int {
        return 0;
    }


    private function createFilterQueryBuilder(VisitFilter $filter) : QueryBuilder {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::VISIT_ALIAS);
        $queryBuilder->addCriteria($filter->buildCriteria());

        if (!empty($filter->getVisitor())) {
            $this->joinVisitor($queryBuilder, $filter->getVisitor());
        }

        return $queryBuilder;
    }


    private function joinVisitor(QueryBuilder &$queryBuilder, User $visitor) {
        $queryBuilder->join(self::VISIT_ALIAS . ".visitor", self::VISITOR_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::VISITOR_ALIAS, ":visitor"));
        $queryBuilder->setParameter("visitor", $visitor);
    }

}
