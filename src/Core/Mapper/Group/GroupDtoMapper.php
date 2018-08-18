<?php

namespace App\Core\Mapper\Group;

use App\Core\DTO\Group\GroupDto;
use App\Core\Entity\Group\Group;
use App\Core\Entity\User\User;
use App\Core\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class GroupDtoMapper implements DtoMapperInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var GroupPictureDtoMapper */
    private $groupPictureDtoMapper;


    public function __construct(EntityManagerInterface $entityManager, GroupPictureDtoMapper $groupPictureDtoMapper)
    {
        $this->entityManager = $entityManager;
        $this->groupPictureDtoMapper = $groupPictureDtoMapper;
    }


    /**
     * @param Group $entity
     *
     * @return GroupDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new GroupDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setName($entity->getName());
        $dto->setDescription($entity->getDescription());
        $dto->setBudget($entity->getBudget());
        $dto->setStatus($entity->getStatus());
        $dto->setPicture($this->groupPictureDtoMapper->toDto($entity->getPicture()));
        $dto->setCreatorId($entity->getCreator()->getId());

        return $dto;
    }


    /**
     * @param GroupDto $dto
     *
     * @return Group|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $creator = $this->entityManager->find(User::class, $dto->getCreatorId());
        $entity = new Group($creator);

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setName($dto->getName());
        $entity->setDescription($dto->getDescription());
        $entity->setBudget($dto->getBudget());
        $entity->setStatus($dto->getStatus());
        $entity->setPicture($this->groupPictureDtoMapper->toEntity($dto->getPicture()));

        return $entity;
    }

}