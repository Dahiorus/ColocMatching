<?php

namespace App\Core\Repository\Group;

use App\Core\Entity\Group\Group;
use App\Core\Entity\User\User;
use App\Core\Repository\EntityRepository;
use App\Core\Repository\Filter\GroupFilter;
use App\Core\Repository\Filter\Pageable\Pageable;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;

class GroupRepository extends EntityRepository
{
    protected const ALIAS = "g";
    private const CREATOR_ALIAS = "c";
    private const PICTURE_ALIAS = "p";
    private const MEMBERS_ALIAS = "m";


    /**
     * Finds a user's announcements with paging
     *
     * @param User $creator The announcements creator
     * @param Pageable $pageable [optional] Paging information
     * @return Group[]
     */
    public function findByCreator(User $creator, Pageable $pageable = null) : array
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $this->joinCreator($queryBuilder, $creator);

        if (!empty($pageable))
        {
            $this->setPaging($queryBuilder, $pageable);
        }

        $query = $queryBuilder->getQuery();
        $query->useQueryCache(true);

        return $query->getResult();
    }


    /**
     * Counts a user's announcements
     *
     * @param User $creator The announcements creator
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countByCreator(User $creator) : int
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));
        $this->joinCreator($queryBuilder, $creator);

        $query = $queryBuilder->getQuery();
        $query->useQueryCache(true);

        return $query->getSingleScalarResult();
    }


    /**
     * Finds one group with a specific member
     *
     * @param User $member The member
     *
     * @return Group[]
     */
    public function findByMember(User $member)
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $this->havingMember($queryBuilder, $member);

        $query = $queryBuilder->getQuery();
        $query->useQueryCache(true);

        return $query->getResult();
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
        $userAlias = "u";

        // subquery to count users in a group
        $subQb = $this->getEntityManager()->createQueryBuilder();
        $subQb
            ->select($subQb->expr()->count($userAlias))
            ->from(User::class, $userAlias)
            ->where($subQb->expr()->isMemberOf($userAlias, self::ALIAS . ".members"));
        $subQuery = $subQb->getQuery()->getDQL();

        $queryBuilder->join(self::ALIAS . ".members", self::MEMBERS_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->gte("($subQuery)", ":countMembers"));
        $queryBuilder->setParameter("countMembers", $countMembers, Type::INTEGER);
    }


    private function joinCreator(QueryBuilder $queryBuilder, User $creator)
    {
        $queryBuilder->join(self::ALIAS . ".creator", self::CREATOR_ALIAS);
        $queryBuilder->where($queryBuilder->expr()->eq(self::CREATOR_ALIAS, ":creator"));
        $queryBuilder->setParameter("creator", $creator);
    }


    private function havingMember(QueryBuilder $queryBuilder, User $member)
    {
        $queryBuilder->andWhere($queryBuilder->expr()->isMemberOf(":member", self::ALIAS . ".members"));
        $queryBuilder->setParameter("member", $member);
    }

}
