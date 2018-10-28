<?php

namespace App\Core\Manager;

use App\Core\DTO\AbstractDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Repository\Filter\Pageable\Pageable;
use App\Core\Repository\Filter\Searchable;
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
     * @return Collection|Page
     * @throws ORMException
     */
    public function list(Pageable $pageable = null);


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
     * @return Collection|Page
     * @throws ORMException
     */
    public function search(Searchable $filter, Pageable $pageable = null);


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
     *
     * @throws ORMException
     */
    public function deleteAll(bool $flush = true) : void;

}
