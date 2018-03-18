<?php

namespace ColocMatching\CoreBundle\Manager\Visit;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\Visit\VisitDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visit;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Manager\AbstractDtoManager;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Mapper\Visit\VisitDtoMapper;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Visit\VisitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

abstract class VisitDtoManager extends AbstractDtoManager implements VisitDtoManagerInterface
{
    /** @var VisitDtoMapper */
    protected $dtoMapper;

    /** @var VisitRepository */
    protected $repository;

    /** @var UserDtoMapper */
    protected $userDtoMapper;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, VisitDtoMapper $dtoMapper,
        UserDtoMapper $userDtoMapper)
    {
        parent::__construct($logger, $em, $dtoMapper);
        $this->userDtoMapper = $userDtoMapper;
    }


    /**
     * @inheritdoc
     */
    public function listByVisited(int $visitedId, PageableFilter $filter) : array
    {
        $this->logger->debug("Listing visits done on an entity",
            array ("visited" => array ($this->getVisitedClass() => $visitedId), "filter" => $filter));

        $visited = $this->getVisited($visitedId);
        /** @var Visit[] $visits */
        $visits = $this->repository->findByVisited($visited, $filter);

        return $this->convertEntityListToDto($visits);
    }


    /**
     * @inheritdoc
     */
    public function countByVisited(int $visitedId) : int
    {
        $this->logger->debug("Counting visits done on an entity",
            array ("visited" => array ($this->getVisitedClass() => $visitedId)));

        $visited = $this->getVisited($visitedId);

        return $this->repository->countByVisited($visited);
    }


    /**
     * @inheritdoc
     */
    public function listByVisitor(UserDto $visitor, PageableFilter $filter) : array
    {
        $this->logger->debug("Listing visits done by a visitor",
            array ("visitedClass" => $this->getVisitedClass(), "visitor" => $visitor, "filter" => $filter));

        /** @var User $userEntity */
        $userEntity = $this->userDtoMapper->toEntity($visitor);
        /** @var Visit[] $visits */
        $visits = $this->repository->findByVisitor($userEntity, $filter);

        return $this->convertEntityListToDto($visits);
    }


    /**
     * @inheritdoc
     */
    public function countByVisitor(UserDto $visitor) : int
    {
        $this->logger->debug("Counting visits done by a visitor",
            array ("visitedClass" => $this->getVisitedClass(), "visitor" => $visitor));

        /** @var User $userEntity */
        $userEntity = $this->userDtoMapper->toEntity($visitor);

        return $this->repository->countByVisitor($userEntity);
    }


    /**
     * @inheritdoc
     */
    public function create(int $visitedId, UserDto $visitor, bool $flush = true) : VisitDto
    {
        $this->logger->debug("Creating a visit",
            array ("visited" => array ($this->getVisitedClass() => $visitedId), "visitor" => $visitor));

        $visited = $this->getVisited($visitedId);
        $visitorEntity = $this->em->find(User::class, $visitor->getId());
        $visit = Visit::create($visited, $visitorEntity);

        $this->em->persist($visit);
        $this->flush($flush);

        return $this->dtoMapper->toDto($visit);
    }


    /**
     * @inheritdoc
     */
    public function deleteVisitableVisits(int $visitedId, bool $flush = true) : int
    {
        $this->logger->debug("Deleting all visits done on a visitable",
            array ("visited" => array ($this->getVisitedClass() => $visitedId), "flush" => $flush));

        $visited = $this->getVisited($visitedId);
        $count = $this->repository->deleteAllOfVisited($visited);
        $this->flush($flush);

        $this->logger->debug(sprintf("%d visit(s) deleted", $count));

        return $count;
    }


    /**
     * Gets a Visitable by its identifier
     *
     * @param int $id The visitable identifier
     *
     * @return Visitable
     * @throws EntityNotFoundException
     */
    private function getVisited(int $id)
    {
        /** @var Visitable $visited */
        $visited = $this->em->find($this->getVisitedClass(), $id);

        if (empty($visited))
        {
            throw new EntityNotFoundException($this->getVisitedClass(), "id", $id);
        }

        return $visited;
    }


    /**
     * Gets the visited entity class
     * @return string
     */
    protected abstract function getVisitedClass() : string;
}