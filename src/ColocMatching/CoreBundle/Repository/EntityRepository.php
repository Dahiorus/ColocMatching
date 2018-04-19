<?php

namespace ColocMatching\CoreBundle\Repository;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Repository\Filter\Pageable;
use ColocMatching\CoreBundle\Repository\Filter\Searchable;
use Doctrine\ORM\EntityRepository as BaseRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;

/**
 * Abstract repository
 *
 * @author Dahiorus
 */
abstract class EntityRepository extends BaseRepository
{
    protected const ALIAS = "e";


    /**
     * Finds entities with paging
     *
     * @param Pageable [optional] $pageable Paging information
     *
     * @return EntityInterface[]
     */
    public function findPage(Pageable $pageable = null) : array
    {
        $queryBuilder = $this->createQueryBuilder(static::ALIAS);

        if (!empty($pageable))
        {
            $this->setPaging($queryBuilder, $pageable);
        }

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
        $queryBuilder = $this->createQueryBuilder(static::ALIAS);

        $queryBuilder->select($queryBuilder->expr()->countDistinct(static::ALIAS));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    /**
     * Finds specific entities
     *
     * @param Searchable $filter Criteria filter
     * @param Pageable $pageable [optional] Paging information
     *
     * @return EntityInterface[]
     * @throws ORMException
     */
    public function findByFilter(Searchable $filter, Pageable $pageable = null) : array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter);

        if (!empty($pageable))
        {
            $this->setPaging($queryBuilder, $pageable);
        }

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
        $queryBuilder->select($queryBuilder->expr()->countDistinct(static::ALIAS));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    protected function setPaging(QueryBuilder $queryBuilder, PageableFilter $filter)
    {
        $queryBuilder->setMaxResults($pageable->getSize())
            ->setFirstResult($pageable->getOffset());

        foreach ($pageable->getSort() as $property => $order)
        {
            $queryBuilder->addOrderBy(static::ALIAS . ".$property", $order);
        }
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
