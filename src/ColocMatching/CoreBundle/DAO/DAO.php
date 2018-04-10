<?php

namespace ColocMatching\CoreBundle\DAO;

use ColocMatching\CoreBundle\Entity\AbstractEntity;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\Searchable;
use Doctrine\ORM\ORMException;

interface DAO
{
    /**
     * Gets instances of an entity with pagination filter
     *
     * @param PageableFilter $filter The pagination filter
     *
     * @return AbstractEntity[]
     */
    public function list(PageableFilter $filter) : array;


    /**
     * Gets all entities
     *
     * @return AbstractEntity[]
     */
    public function findAll() : array;


    /**
     * Counts all instances of an entity
     *
     * @return int
     * @throws ORMException
     */
    public function countAll() : int;


    /**
     * Searches entities corresponding to the filter
     *
     * @param Searchable $filter The criteria filter
     *
     * @return AbstractEntity[]
     * @throws ORMException
     */
    public function search(Searchable $filter) : array;


    /**
     * Counts instances corresponding to the filter
     *
     * @param Searchable $filter The criteria filter
     *
     * @return int
     * @throws ORMException
     */
    public function countBy(Searchable $filter) : int;


    /**
     * Finds one entity by criteria
     *
     * @param array $criteria The criteria filter
     *
     * @return AbstractEntity|null
     */
    public function findOne(array $criteria = array ());


    /**
     * Saves an entity
     *
     * @param AbstractEntity $entity The entity to save
     *
     * @return AbstractEntity
     * @return ORMException
     */
    public function save(AbstractEntity $entity) : AbstractEntity;


    /**
     * Gets one instance of an entity by its identifier. Does not make a query to the database.
     *
     * @param int $id The identifier of the instance
     *
     * @return AbstractEntity
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function get(int $id) : AbstractEntity;


    /**
     * Finds one instance of an entity by its identifier
     *
     * @param int $id The identifier of the instance
     *
     * @return AbstractEntity
     * @throws EntityNotFoundException
     */
    public function read(int $id) : AbstractEntity;


    /**
     * Deletes an entity
     *
     * @param AbstractEntity $entity The entity to delete
     */
    public function delete(AbstractEntity $entity) : void;


    /**
     * Deletes all entities
     */
    public function deleteAll() : void;


    /**
     * Flushes all the entity manager operations
     */
    public function flush() : void;
}