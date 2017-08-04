<?php

namespace ColocMatching\CoreBundle\Repository\Group;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;

class GroupRepository extends EntityRepository {

    protected const ALIAS = "g";

    private const PICTURE_ALIAS = "p";

    private const MEMBERS_ALIAS = "m";


    public function findByFilter(GroupFilter $filter, array $fields = null) : array {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter);
        $this->setPagination($queryBuilder, $filter, self::ALIAS);

        if (!empty($fields)) {
            $queryBuilder->select($this->getReturnedFields(self::ALIAS, $fields));
        }

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByFilter(GroupFilter $filter) : int {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter);
        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    private function createFilterQueryBuilder(GroupFilter $filter) : QueryBuilder {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->addCriteria($filter->buildCriteria());

        if ($filter->withPicture()) {
            $this->withPictureOnly($queryBuilder);
        }

        if (!is_null($filter->getCountMembers())) {
            $this->joinCountMembers($queryBuilder, $filter->getCountMembers());
        }

        return $queryBuilder;
    }


    private function withPictureOnly(QueryBuilder &$queryBuilder) {
        $queryBuilder->join(self::ALIAS . ".picture", self::PICTURE_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->isNotNull(self::PICTURE_ALIAS));
    }


    private function joinCountMembers(QueryBuilder &$queryBuilder, int $countMembers) {
        $subQuery = $this->getEntityManager()->createQuery(
            sprintf("SELECT count(u.id) FROM %s AS u WHERE u MEMBER OF %s.members", User::class, self::ALIAS))
            ->getDQL();

        $queryBuilder->join(self::ALIAS . ".members", self::MEMBERS_ALIAS);
        $queryBuilder->andWhere("($subQuery) >= :countMembers");
        $queryBuilder->setParameter("countMembers", $countMembers, Type::INTEGER);
    }

}