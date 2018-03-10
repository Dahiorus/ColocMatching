<?php

namespace ColocMatching\CoreBundle\Manager\Visit;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visit;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Exception\VisitNotFoundException;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\CoreBundle\Repository\Visit\VisitRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

/**
 * CRUD manager of the entity Visit
 *
 * @author Dahiorus
 * @deprecated
 */
class VisitManager implements VisitManagerInterface
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var VisitRepository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(ObjectManager $manager, string $entityClass, LoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->repository = $this->manager->getRepository($entityClass);
        $this->logger = $logger;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::list()
     */
    public function list(PageableFilter $filter, array $fields = null) : array
    {
        $this->logger->debug("Listing visits with pagination", array ("filter" => $filter, "fields" => $fields));

        return $this->repository->findPage($filter, $fields);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countAll()
     */
    public function countAll() : int
    {
        $this->logger->debug("Counting all visits");

        return $this->repository->countAll();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface::listByVisited()
     */
    public function listByVisited(Visitable $visited, PageableFilter $filter) : array
    {
        $this->logger->info("List visits by visited", array ("visited" => $visited, "filter" => $filter));

        return $this->repository->findByVisited($visited, $filter);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface::countByVisited()
     */
    public function countByVisited(Visitable $visited) : int
    {
        $this->logger->debug("Counting all visits done on an entity", array ("visited" => $visited));

        return $this->repository->countByVisited($visited);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface::listByVisitor()
     */
    public function listByVisitor(User $visitor, PageableFilter $filter) : array
    {
        $this->logger->info("List visits by visitor", array ("visitor" => $visitor, "filter" => $filter));

        return $this->repository->findByVisitor($visitor, $filter);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface::countByVisitor()
     */
    public function countByVisitor(User $visitor) : int
    {
        $this->logger->debug("Counting all visits done by a visitor", array ("visitor" => $visitor));

        return $this->repository->countByVisitor($visitor);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface::create()
     */
    public function create(Visitable $visited, User $visitor) : Visit
    {
        $this->logger->debug("Creating a new visit", array ("visited" => $visited, "visitor" => $visitor));

        /** @var Visit */
        $visit = Visit::create($visited, $visitor);

        $this->manager->persist($visit);
        $this->manager->flush();

        return $visit;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::read()
     */
    public function read(int $id, array $fields = null)
    {
        $this->logger->debug("Getting an existing visit", array ("id" => $id, "fields" => $fields));

        /** @var Visit */
        $visit = $this->repository->findById($id, $fields);

        if (empty($visit))
        {
            throw new VisitNotFoundException("id", $id);
        }

        return $visit;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface::search()
     */
    public function search(VisitFilter $filter, array $fields = null) : array
    {
        $this->logger->debug("Searching visits by filtering", array ("filter" => $filter, "fields" => $fields));

        return $this->repository->findByFilter($filter, $fields);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface::countBy()
     */
    public function countBy(VisitFilter $filter) : int
    {
        $this->logger->debug("Counting visits by filtering", array ("filter" => $filter));

        return $this->repository->countByFilter($filter);
    }

}
