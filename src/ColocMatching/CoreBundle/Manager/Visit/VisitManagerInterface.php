<?php

namespace ColocMatching\CoreBundle\Manager\Visit;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visit;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Manager\ManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;

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


    /**
     * Searches visits corresponding to the filter
     *
     * @param VisitFilter $filter
     * @param array $fields
     *
     * @return array<Visit>
     */
    public function search(VisitFilter $filter, array $fields = null) : array;


    /**
     * Counts instances corresponding to the filter
     *
     * @param VisitFilter $filter The search filter
     *
     * @return int
     */
    public function countBy(VisitFilter $filter) : int;

}