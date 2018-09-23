<?php

namespace App\Core\Repository\Tag;

use App\Core\Entity\Tag\Tag;
use App\Core\Repository\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

class TagRepository extends EntityRepository
{
    protected const ALIAS = "t";


    /**
     * Finds one Tag by value
     *
     * @param string $value The tag value
     *
     * @return Tag|null
     * @throws NonUniqueResultException
     */
    public function findOneByValue(string $value)
    {
        $qb = $this->createQueryBuilder(self::ALIAS);

        $qb->where($qb->expr()->eq(self::ALIAS . ".value", ":value"));
        $qb->setParameter("value", $value);

        $query = $qb->getQuery();
        $query->useQueryCache(true);

        return $query->getOneOrNullResult();
    }


    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }

}
