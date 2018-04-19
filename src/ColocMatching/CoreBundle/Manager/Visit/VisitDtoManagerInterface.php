<?php

namespace ColocMatching\CoreBundle\Manager\Visit;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\Visit\VisitDto;
use ColocMatching\CoreBundle\DTO\VisitableDto;
use ColocMatching\CoreBundle\Manager\DtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\Pageable;
use Doctrine\ORM\ORMException;

interface VisitDtoManagerInterface extends DtoManagerInterface
{
    /**
     * Lists with pagination the visits done on one visited entity
     *
     * @param VisitableDto $visited The visited entity
     * @param Pageable $pageable [optional] Paging information
     *
     * @return VisitDto[]
     * @throws ORMException
     */
    public function listByVisited(VisitableDto $visited, Pageable $pageable = null) : array;


    /**
     * Counts the visits done on one visited entity
     *
     * @param VisitableDto $visited The visited entity
     *
     * @return int
     * @throws ORMException
     */
    public function countByVisited(VisitableDto $visited) : int;


    /**
     * Lists with pagination the visits done by one visitor
     *
     * @param UserDto $visitor The visitor
     * @param Pageable $pageable [optional] Paging information
     *
     * @return VisitDto[]
     */
    public function listByVisitor(UserDto $visitor, Pageable $pageable = null) : array;


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
     * Creates a visit on the visited entity by the specified visitor
     *
     * @param UserDto $visitor The visitor
     * @param VisitableDto $visited The visited entity
     * @param bool $flush If the operation must be flushed
     *
     * @return VisitDto
     */
    public function create(UserDto $visitor, VisitableDto $visited, bool $flush = true) : VisitDto;

}
