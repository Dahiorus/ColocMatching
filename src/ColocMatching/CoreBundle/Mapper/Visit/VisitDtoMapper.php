<?php

namespace ColocMatching\CoreBundle\Mapper\Visit;

use ColocMatching\CoreBundle\DTO\Visit\AnnouncementVisitDto;
use ColocMatching\CoreBundle\DTO\Visit\GroupVisitDto;
use ColocMatching\CoreBundle\DTO\Visit\UserVisitDto;
use ColocMatching\CoreBundle\DTO\Visit\VisitDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\AnnouncementVisit;
use ColocMatching\CoreBundle\Entity\Visit\GroupVisit;
use ColocMatching\CoreBundle\Entity\Visit\UserVisit;
use ColocMatching\CoreBundle\Entity\Visit\Visit;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class VisitDtoMapper implements DtoMapperInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @param Visit $entity
     *
     * @return VisitDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = self::createVisitDto($entity);

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setVisitedId($entity->getVisited()->getId());
        $dto->setVisitorId($entity->getVisitor()->getId());

        return $dto;
    }


    /**
     * @param VisitDto $dto
     *
     * @return Visit|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        /** @var Visitable $visited */
        $visited = $this->entityManager->find($dto->getVisitedClass(), $dto->getVisitedId());
        $visitor = $this->entityManager->find(User::class, $dto->getVisitorId());
        $entity = Visit::create($visited, $visitor);

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());

        return $entity;
    }


    /**
     * Creates an instance of VisitDto depending on the Visit class
     *
     * @param Visit $entity The visit
     *
     * @return VisitDto
     */
    private static function createVisitDto(Visit $entity) : VisitDto
    {
        if ($entity instanceof AnnouncementVisit)
        {
            return new AnnouncementVisitDto();
        }

        if ($entity instanceof GroupVisit)
        {
            return new GroupVisitDto();
        }

        if ($entity instanceof UserVisit)
        {
            return new UserVisitDto();
        }

        throw new \InvalidArgumentException("'" . get_class($entity) . "' not supported");
    }
}