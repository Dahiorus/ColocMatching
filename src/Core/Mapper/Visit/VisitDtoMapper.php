<?php

namespace App\Core\Mapper\Visit;

use App\Core\DTO\Visit\VisitDto;
use App\Core\Entity\User\User;
use App\Core\Entity\Visit\Visit;
use App\Core\Entity\Visit\Visitable;
use App\Core\Mapper\DtoMapperInterface;
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

        $dto = new VisitDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setVisitedId($entity->getVisitedId());
        $dto->setVisitedClass($entity->getVisitedClass());
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
        $visitor = $this->entityManager->find(User::class, $dto->getVisitorId());
        $entity = new Visit($dto->getVisitedClass(), $dto->getVisitedId(), $visitor);

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());

        return $entity;
    }

}
