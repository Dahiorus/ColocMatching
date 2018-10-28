<?php

namespace App\Core\Manager\Visit;

use App\Core\DTO\User\UserDto;
use App\Core\DTO\Visit\VisitableDto;
use App\Core\DTO\Visit\VisitDto;
use App\Core\Manager\Collection;
use App\Core\Manager\DtoManagerInterface;
use App\Core\Manager\Page;
use App\Core\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\ORMException;

interface VisitDtoManagerInterface extends DtoManagerInterface
{
    /**
     * Lists with pagination the visits done on one visited entity
     *
     * @param VisitableDto $visited The visited entity
     * @param Pageable $pageable [optional] Paging information
     *
     * @return Collection|Page
     * @throws ORMException
     */
    public function listByVisited(VisitableDto $visited, Pageable $pageable = null);


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
     * @return Collection|Page
     * @throws ORMException
     */
    public function listByVisitor(UserDto $visitor, Pageable $pageable = null);


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
