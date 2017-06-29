<?php

namespace ColocMatching\CoreBundle\Manager\Visit;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use ColocMatching\CoreBundle\Entity\Visit\Visit;

/**
 * CRUD manager of the entity Visit
 *
 * @author Dahiorus
 */
class VisitManager implements VisitManagerInterface {

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(ObjectManager $manager, string $entityClass, LoggerInterface $logger) {
        $this->manager = $manager;
        $this->repository = $this->manager->getRepository($entityClass);
        $this->logger = $logger;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::list()
     */
    public function list(PageableFilter $filter, array $fields = null): array {
        // TODO Auto-generated method stub
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countAll()
     */
    public function countAll(): int {
        // TODO Auto-generated method stub
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface::listByVisited()
     */
    public function listByVisited(Visitable $visited, PageableFilter $filter): array {
        // TODO Auto-generated method stub
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface::listByVisitor()
     */
    public function listByVisitor(User $visitor, PageableFilter $filter): array {
        // TODO Auto-generated method stub
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface::create()
     */
    public function create(Visitable $visited, User $visitor): Visit {
        $this->logger->debug("Creating a new visit", array ("visited" => $visited, "visitor" => $visitor));

        $visit = new Visit($visitor);
        $visit->setVisitedAt(new \DateTime());

        $visited->addVisit($visit);

        $this->manager->persist($visit);
        $this->manager->flush();

        return $visit;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::read()
     */
    public function read(int $id, array $fields = null) {
        // TODO Auto-generated method stub
    }

}