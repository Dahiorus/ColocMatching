<?php

namespace ColocMatching\CoreBundle\Manager;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\Pageable;
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
     * Gets entities
     *
     * @param Pageable $pageable [optional] Paging information
     *
     * @return AbstractDto[]
     */
    public function list(Pageable $pageable = null) : array;


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
     * @param Pageable $pageable [optional] Paging information
     *
     * @return AbstractDto[]
     * @throws ORMException
     */
    public function search(Searchable $filter, Pageable $pageable = null) : array;


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
     * Deletes an entity
     *
     * @param AbstractDto $dto The entity to delete
     * @param bool $flush If the operation must be flushed
     *
     * @throws EntityNotFoundException
     */
    public function delete(AbstractDto $dto, bool $flush = true) : void;


    /**
     * Deletes all entities
     *
     * @param bool $flush If the operation must be flushed
     */
    public function deleteAll(bool $flush = true) : void;

}
