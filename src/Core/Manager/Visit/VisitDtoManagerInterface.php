<?php

namespace App\Core\Manager\Visit;

use App\Core\DTO\Collection;
use App\Core\DTO\Page;
use App\Core\DTO\User\UserDto;
use App\Core\DTO\Visit\VisitableDto;
use App\Core\DTO\Visit\VisitDto;
use App\Core\Manager\DtoManagerInterface;
use App\Core\Repository\Filter\Pageable\Pageable;
use App\Core\Repository\Filter\VisitFilter;
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
     * Counts the visits matching the filter
     *
     * @param VisitFilter $filter The filter
     *
     * @return int
     * @throws ORMException
     */
    public function countByFilter(VisitFilter $filter) : int;


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
