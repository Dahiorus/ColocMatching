<?php

namespace ColocMatching\CoreBundle\Manager\Visit;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\Visit\VisitDto;
use ColocMatching\CoreBundle\DTO\VisitableDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visit;
use ColocMatching\CoreBundle\Manager\AbstractDtoManager;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Mapper\Visit\VisitDtoMapper;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\CoreBundle\Repository\Visit\VisitRepository;
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
    public function listByVisited(VisitableDto $visited, PageableFilter $filter) : array
    {
        $this->logger->debug("Listing visits done on an entity", array ("visited" => $visited, "filter" => $filter));

        $visitFilter = new VisitFilter();
        $visitFilter->setPage($filter->getPage());
        $visitFilter->setSize($filter->getSize());
        $visitFilter->setSort($filter->getSort());
        $visitFilter->setOrder($filter->getOrder());
        $visitFilter->setVisitedClass($visited->getEntityClass());
        $visitFilter->setVisitedId($visited->getId());

        /** @var Visit[] $visits */
        $visits = $this->repository->findByFilter($visitFilter);

        return $this->convertEntityListToDto($visits);
    }


    /**
     * @inheritdoc
     */
    public function countByVisited(VisitableDto $visited) : int
    {
        $this->logger->debug("Listing visits done on an entity", array ("visited" => $visited));

        $visitFilter = new VisitFilter();
        $visitFilter->setVisitedClass($visited->getEntityClass());
        $visitFilter->setVisitedId($visited->getId());

        return $this->repository->countByFilter($visitFilter);
    }


    /**
     * @inheritdoc
     */
    public function listByVisitor(UserDto $visitor, PageableFilter $filter) : array
    {
        $this->logger->debug("Listing visits done by a visitor", array ("visitor" => $visitor, "filter" => $filter));

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

        return $this->dtoMapper->toDto($entity);
    }


    protected function getDomainClass() : string
    {
        return Visit::class;
    }

}
