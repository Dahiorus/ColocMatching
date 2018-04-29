<?php

namespace ColocMatching\CoreBundle\DAO;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visit;
use ColocMatching\CoreBundle\Repository\Filter\Pageable;
use ColocMatching\CoreBundle\Repository\Visit\VisitRepository;

class VisitDao extends EntityDao
{
    /** @var VisitRepository */
    protected $repository;


    protected function getDomainClass() : string
    {
        return Visit::class;
    }


    /**
     * Finds visits with the specified visitor
     *
     * @param User $user The visitor
     * @param Pageable $pageable Paging filter
     *
     * @return Visit[]
     */
    public function findByVisitor(User $user, Pageable $pageable = null) : array
    {
        return $this->repository->findByVisitor($user, $pageable);
    }


    /**
     * Counts all visits with the specified visitor
     *
     * @param User $user The visitor
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countByVisitor(User $user) : int
    {
        return $this->repository->countByVisitor($user);
    }

}
