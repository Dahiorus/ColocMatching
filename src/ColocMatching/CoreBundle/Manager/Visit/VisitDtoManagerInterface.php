<?php

namespace ColocMatching\CoreBundle\Manager\Visit;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\Visit\VisitDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Manager\DtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Doctrine\ORM\ORMException;

interface VisitDtoManagerInterface extends DtoManagerInterface
{
    /**
     * Lists with pagination the visits done on one visited entity
     *
     * @param int $visitedId The visited entity identifier
     * @param PageableFilter $filter Paging information
     *
     * @return VisitDto[]
     * @throws EntityNotFoundException
     */
    public function listByVisited(int $visitedId, PageableFilter $filter) : array;


    /**
     * Counts the visits done on one visited entity
     *
     * @param int $visitedId The visited entity identifier
     *
     * @return int
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function countByVisited(int $visitedId) : int;


    /**
     * Lists with pagination the visits done by one visitor
     *
     * @param UserDto $visitor The visitor
     * @param PageableFilter $filter Paging information
     *
     * @return VisitDto[]
     */
    public function listByVisitor(UserDto $visitor, PageableFilter $filter) : array;


    /**
     * Counts the visits done by one visitor
     *
     * @param UserDto $visitor The visitor
     *
     * @return int
     * @throws ORMException
     */
    public function countByVisitor(UserDto $visitor) : int;


    /**
     * Creates a visit on a visitable
     *
     * @param int $visitedId The visitable identifier
     * @param UserDto $visitor The visitor
     * @param bool $flush If the operation must be flushed
     *
     * @return VisitDto
     * @throws EntityNotFoundException
     */
    public function create(int $visitedId, UserDto $visitor, bool $flush = true) : VisitDto;


    /**
     * Deletes all visits done on a visitable
     *
     * @param int $visitedId The visitable identifier
     * @param bool $flush If the operation must be flushed
     *
     * @return int
     * @throws EntityNotFoundException
     */
    public function deleteVisitableVisits(int $visitedId, bool $flush = true) : int;

}
