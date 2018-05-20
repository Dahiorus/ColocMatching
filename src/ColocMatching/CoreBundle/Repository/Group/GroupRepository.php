<?php

namespace ColocMatching\CoreBundle\Repository\Group;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;

class GroupRepository extends EntityRepository
{
    protected const ALIAS = "g";
    private const PICTURE_ALIAS = "p";
    private const MEMBERS_ALIAS = "m";


    /**
     * Finds one group with a specific member
     *
     * @param User $member The member
     *
     * @return null|Group
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByMember(User $member)
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $this->joinMember($queryBuilder, $member);

        $query = $queryBuilder->getQuery();
        $this->configureCache($query);

        return $query->getOneOrNullResult();
    }


    /**
     * @param GroupFilter $filter
     *
     * @return QueryBuilder
     * @throws \Doctrine\ORM\Query\QueryException
     */
    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->addCriteria($filter->buildCriteria());

        if ($filter->withPicture())
        {
            $this->withPictureOnly($queryBuilder);
        }

        if (!is_null($filter->getCountMembers()))
        {
            $this->joinCountMembers($queryBuilder, $filter->getCountMembers());
        }

        return $queryBuilder;
    }


    private function withPictureOnly(QueryBuilder $queryBuilder)
    {
        $queryBuilder->join(self::ALIAS . ".picture", self::PICTURE_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->isNotNull(self::PICTURE_ALIAS));
    }


    private function joinCountMembers(QueryBuilder $queryBuilder, int $countMembers)
    {
        $subQuery = $this->getEntityManager()->createQuery(
            sprintf("SELECT count(u.id) FROM %s AS u WHERE u MEMBER OF %s.members", User::class,
                self::ALIAS))->getDQL();

        $queryBuilder->join(self::ALIAS . ".members", self::MEMBERS_ALIAS);
        $queryBuilder->andWhere("($subQuery) >= :countMembers");
        $queryBuilder->setParameter("countMembers", $countMembers, Type::INTEGER);
    }


    private function joinMember(QueryBuilder &$queryBuilder, User $member)
    {
        $queryBuilder->andWhere($queryBuilder->expr()->isMemberOf(":member", self::ALIAS . ".members"));
        $queryBuilder->setParameter("member", $member);
    }

}