<?php

namespace App\Core\Manager\Visit;

use App\Core\DTO\User\UserDto;
use App\Core\DTO\Visit\VisitableDto;
use App\Core\DTO\Visit\VisitDto;
use App\Core\Entity\User\User;
use App\Core\Entity\Visit\Visit;
use App\Core\Manager\AbstractDtoManager;
use App\Core\Mapper\User\UserDtoMapper;
use App\Core\Mapper\Visit\VisitDtoMapper;
use App\Core\Repository\Filter\Pageable\Pageable;
use App\Core\Repository\Filter\VisitFilter;
use App\Core\Repository\Visit\VisitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Model manager of VisitDto
 *
 * @author Dahiorus
 */
class VisitDtoManager extends AbstractDtoManager implements VisitDtoManagerInterface
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
    public function listByVisited(VisitableDto $visited, Pageable $pageable = null) : array
    {
        $this->logger->debug("Listing visits done on an entity", array ("visited" => $visited, "filter" => $pageable));

        $visitFilter = new VisitFilter();
        $visitFilter->setVisitedClass($visited->getEntityClass());
        $visitFilter->setVisitedId($visited->getId());

        /** @var Visit[] $visits */
        $visits = $this->repository->findByFilter($visitFilter, $pageable);

        $this->logger->debug("Visits found", array ("count" => count($visits)));

        return $this->convertEntityListToDto($visits);
    }


    /**
     * @inheritdoc
     */
    public function countByVisited(VisitableDto $visited) : int
    {
        $this->logger->debug("Counting visits done on an entity", array ("visited" => $visited));

        $visitFilter = new VisitFilter();
        $visitFilter->setVisitedClass($visited->getEntityClass());
        $visitFilter->setVisitedId($visited->getId());

        return $this->repository->countByFilter($visitFilter);
    }


    /**
     * @inheritdoc
     */
    public function listByVisitor(UserDto $visitor, Pageable $pageable = null) : array
    {
        $this->logger->debug("Listing visits done by a visitor", array ("visitor" => $visitor, "filter" => $pageable));

        /** @var User $userEntity */
        $userEntity = $this->userDtoMapper->toEntity($visitor);
        /** @var Visit[] $visits */
        $visits = $this->repository->findByVisitor($userEntity, $pageable);

        $this->logger->debug("Visits found", array ("count" => count($visits)));

        return $this->convertEntityListToDto($visits);
    }


    /**
     * @inheritdoc
     */
    public function countByVisitor(UserDto $visitor) : int
    {
        $this->logger->debug("Counting visits done by a visitor", array ("visitor" => $visitor));

        /** @var User $userEntity */
        $userEntity = $this->userDtoMapper->toEntity($visitor);

        return $this->repository->countByVisitor($userEntity);
    }


    /**
     * @inheritdoc
     */
    public function create(UserDto $visitor, VisitableDto $visited, bool $flush = true) : VisitDto
    {
        $this->logger->debug("Creating a new visit", array ("visitor" => $visitor, "visited" => $visited));

        $entity = $this->dtoMapper->toEntity(VisitDto::create($visitor, $visited));

        $this->em->persist($entity);
        $this->flush($flush);

        $this->logger->info("Visit created", array ("visit" => $entity));

        return $this->dtoMapper->toDto($entity);
    }


    protected function getDomainClass() : string
    {
        return Visit::class;
    }

}
