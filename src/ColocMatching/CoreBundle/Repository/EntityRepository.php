<?php

namespace ColocMatching\CoreBundle\Repository;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\Searchable;
use Doctrine\ORM\EntityRepository as BaseRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;

/**
 * Abstract repository
 *
 * @author brondon.ung
 */
abstract class EntityRepository extends BaseRepository
{
    protected const ALIAS = "e";


    /**
     * Finds entities with paging
     *
     * @param PageableFilter $filter Paging information
     *
     * @return EntityInterface[]
     */
    public function findPage(PageableFilter $filter) : array
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->setPaging($queryBuilder, $filter);

        return $queryBuilder->getQuery()->getResult();
    }


    /**
     * Counts all entities
     *
     * @return int The entities count
     * @throws ORMException
     */
    public function countAll() : int
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    /**
     * Finds specific entities
     *
     * @param Searchable $filter
     *
     * @return EntityInterface[]
     * @throws ORMException
     */
    public function findByFilter(Searchable $filter) : array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter);

        /** @var PageableFilter $filter */
        $this->setPaging($queryBuilder, $filter);

        return $queryBuilder->getQuery()->getResult();
    }


    /**
     * Counts specific entities
     *
     * @param Searchable $filter
     *
     * @return int
     * @throws ORMException
     */
    public function countByFilter(Searchable $filter) : int
    {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter);
        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    protected function setPaging(QueryBuilder $queryBuilder, PageableFilter $filter)
    {
        $queryBuilder->setMaxResults($filter->getSize())
            ->setFirstResult($filter->getOffset());
        $queryBuilder->orderBy(self::ALIAS . "." . $filter->getSort(), $filter->getOrder());
    }


    /**
     * Creates a query builder from the criteria filter
     *
     * @param Searchable $filter The criteria filter
     *
     * @return QueryBuilder
     * @throws ORMException
     */
    abstract protected function createFilterQueryBuilder($filter) : QueryBuilder;

}
