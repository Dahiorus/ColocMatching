<?php

namespace App\Core\Repository\Alert;

use App\Core\Entity\Alert\Alert;
use App\Core\Entity\User\User;
use App\Core\Repository\EntityRepository;
use App\Core\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\QueryBuilder;

class AlertRepository extends EntityRepository
{
    protected const ALIAS = "al";
    private const USER_ALIAS = "u";


    /**
     * Finds alerts owned by the specified user and with paging
     *
     * @param User $user The alerts' owner
     * @param Pageable|null $pageable Paging information
     *
     * @return Alert[]
     */
    public function findByUser(User $user, Pageable $pageable = null) : array
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder(self::ALIAS);

        $this->joinUser($qb, $user);

        if (!empty($pageable))
        {
            $this->setPaging($qb, $pageable);
        }

        $query = $qb->getQuery();
        $query->useQueryCache(true);

        return $query->getResult();
    }


    /**
     * Counts all alerts owned by the specified user
     *
     * @param User $user The user
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countByUser(User $user) : int
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder(self::ALIAS);
        $qb->select($qb->expr()->countDistinct(static::ALIAS));

        $this->joinUser($qb, $user);

        $query = $qb->getQuery();
        $query->useQueryCache(true);

        return $query->getSingleScalarResult();
    }


    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }


    public function joinUser(QueryBuilder $qb, User $user)
    {
        $qb->join(self::ALIAS . ".user", self::USER_ALIAS);
        $qb->andWhere($qb->expr()->eq(self::USER_ALIAS, ":user"));
        $qb->setParameter("user", $user);
    }

}
