<?php

namespace App\Core\Repository\User;

use App\Core\Repository\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

class UserTokenRepository extends EntityRepository
{
    protected const ALIAS = "t";


    /**
     * Counts all user tokens having an expiration date before the given date time
     *
     * @param \DateTimeImmutable $dateTime The date time to compare to
     *
     * @return int
     * @throws NonUniqueResultException
     */
    public function countBefore(\DateTimeImmutable $dateTime) : int
    {
        $qb = $this->createQueryBuilder(self::ALIAS);

        $qb->select($qb->expr()->countDistinct(self::ALIAS));
        $this->before($qb, $dateTime);

        return $qb->getQuery()->getSingleScalarResult();
    }


    /**
     * Deletes all user tokens having an expiration date before the given date time
     *
     * @param \DateTimeImmutable $dateTime The date time to compare to
     *
     * @return int
     */
    public function deleteBefore(\DateTimeImmutable $dateTime) : int
    {
        $qb = $this->createQueryBuilder(self::ALIAS);

        $qb->delete();
        $this->before($qb, $dateTime);

        return $qb->getQuery()->execute();
    }


    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        return $this->createQueryBuilder(static::ALIAS);
    }


    private function before(QueryBuilder $qb, \DateTimeImmutable $dateTime) : void
    {
        $qb->where($qb->expr()->lt(self::ALIAS . ".expirationDate", ":date"));
        $qb->setParameter("date", $dateTime);
    }

}
