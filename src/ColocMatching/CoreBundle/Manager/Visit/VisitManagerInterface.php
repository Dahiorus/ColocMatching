<?php

namespace ColocMatching\CoreBundle\Manager\Visit;

use ColocMatching\CoreBundle\Manager\ManagerInterface;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visit;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;

interface VisitManagerInterface extends ManagerInterface {


    /**
     * Creates a visit
     *
     * @param Visitable $visited The visited entity
     * @param User $visitor The visitor
     * @return Visit
     */
    public function create(Visitable $visited, User $visitor): Visit;


    /**
     * Lists with pagination the visits of one visited entity
     *
     * @param Visitable $visited The visited entity
     * @param PageableFilter $filter Pagination information
     * @return array<Visit>
     */
    public function listByVisited(Visitable $visited, PageableFilter $filter): array;


    /**
     * Lists with pagination the visits of one visitor
     *
     * @param User $visitor The visitor
     * @param PageableFilter $filter Pagination information
     * @return array<Visit>
     */
    public function listByVisitor(User $visitor, PageableFilter $filter): array;

}