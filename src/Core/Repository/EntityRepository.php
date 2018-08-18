<?php

namespace App\Core\Repository;

use App\Core\Entity\EntityInterface;
use App\Core\Repository\Filter\Pageable\Pageable;
use App\Core\Repository\Filter\Searchable;
use Doctrine\ORM\Cache;
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

        $query = $queryBuilder->getQuery();
        $query->useQueryCache(true);

        return $query->getResult();
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

        $query = $queryBuilder->getQuery();
        $query->useQueryCache(true);

        return $query->getSingleScalarResult();
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

        $query = $queryBuilder->getQuery();
        $query->useQueryCache(true);

        return $query->getResult();
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

        $query = $queryBuilder->getQuery();
        $query->useQueryCache(true);

        return $query->getSingleScalarResult();
    }


    /**
     * Deletes all entities
     *
     * @return int The number of deleted entities
     */
    public function deleteAll() : int
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder(static::ALIAS);
        $queryBuilder->delete();

        /** @var Cache $cache */
        $cache = $queryBuilder->getEntityManager()->getCache();

        if (!empty($cache))
        {
            $cache->evictEntityRegion($this->getEntityName());
        }

        return $queryBuilder->getQuery()->execute();
    }


    /**
     * Sets paging clause to the query
     *
     * @param QueryBuilder $queryBuilder The query builder
     * @param Pageable $pageable Paging information
     */
    protected function setPaging(QueryBuilder $queryBuilder, Pageable $pageable)
    {
        if (!empty($pageable->getSize()))
        {
            $queryBuilder->setMaxResults($pageable->getSize());

            if (!empty($pageable->getPage()))
            {
                $queryBuilder->setFirstResult($pageable->getOffset());
            }
        }

        foreach ($pageable->getSorts() as $sort)
        {
            $property = $sort->getProperty();
            $order = $sort->getDirection();

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
