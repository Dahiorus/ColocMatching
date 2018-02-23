<?php

namespace ColocMatching\CoreBundle\Manager;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\Searchable;
use Doctrine\ORM\ORMException;

/**
 * Interface DtoManagerInterface
 *
 * @author Dahiorus
 */
interface DtoManagerInterface
{
    /**
     * Gets instances of an entity with pagination filter
     *
     * @param PageableFilter $filter The pagination filter
     *
     * @return AbstractDto[]
     */
    public function list(PageableFilter $filter) : array;


    /**
     * Gets all entities
     *
     * @return AbstractDto[]
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
     * @return AbstractDto[]
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
     * Gets one instance of an entity by its identifier
     *
     * @param int $id The identifier of the instance
     *
     * @return AbstractDto
     * @throws EntityNotFoundException
     */
    public function read(int $id) : AbstractDto;


    /**
     * Gets the entity referenced by its identifier
     *
     * @param int $id The identifier of the entity
     *
     * @return AbstractDto
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function get(int $id) : AbstractDto;


    /**
     * Deletes an entity
     *
     * @param AbstractDto $dto The entity to delete
     * @param bool $flush If the operation must be flushed
     */
    public function delete(AbstractDto $dto, bool $flush = true) : void;


    /**
     * Deletes all entities
     */
    public function deleteAll() : void;

}
