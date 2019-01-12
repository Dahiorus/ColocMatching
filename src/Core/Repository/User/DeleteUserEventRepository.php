<?php

namespace App\Core\Repository\User;

use App\Core\Entity\User\DeleteUserEvent;
use App\Core\Entity\User\User;
use App\Core\Repository\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class DeleteUserEventRepository extends EntityRepository
{
    protected const ALIAS = "d";
    private const USER_ALIAS = "u";


    /**
     * Finds all delete user events at the specified date
     *
     * @param \DateTimeImmutable $deleteAt The date
     * @return DeleteUserEvent[]
     */
    public function findByDeleteAt(\DateTimeImmutable $deleteAt) : array
    {
        $qb = $this->createQueryBuilder(self::ALIAS);
        $qb->where($qb->expr()->lte(self::ALIAS . ".deleteAt", ":deleteAt"));
        $qb->setParameter("deleteAt", $deleteAt);

        $query = $qb->getQuery();
        $query->useQueryCache(true);

        return $query->getResult();
    }


    /**
     * Tests if a delete user event exists for the specified user
     *
     * @param User $user The user
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function existsFor(User $user) : bool
    {
        $qb = $this->createQueryBuilder(self::ALIAS);

        $qb->select($qb->expr()->countDistinct(self::ALIAS));
        $qb->join(self::ALIAS . ".user", self::USER_ALIAS);
        $qb->where($qb->expr()->eq(self::USER_ALIAS, ":user"));
        $qb->setParameter("user", $user);

        $query = $qb->getQuery();
        $query->useQueryCache(true);

        return $query->getSingleScalarResult() > 0;
    }


    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }

}
