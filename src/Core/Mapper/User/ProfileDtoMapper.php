<?php

namespace App\Core\Mapper\User;

use App\Core\DTO\User\ProfileDto;
use App\Core\Entity\User\Profile;
use App\Core\Mapper\DtoMapperInterface;

class ProfileDtoMapper implements DtoMapperInterface
{
    /**
     * @param Profile $entity
     *
     * @return ProfileDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new ProfileDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setBirthDate($entity->getBirthDate());
        $dto->setDescription($entity->getDescription());
        $dto->setGender($entity->getGender());
        $dto->setPhoneNumber($entity->getPhoneNumber());
        $dto->setSmoker($entity->isSmoker());
        $dto->setHasJob($entity->hasJob());
        $dto->setHouseProud($entity->isHouseProud());
        $dto->setCook($entity->isCook());
        $dto->setDiet($entity->getDiet());
        $dto->setSocialStatus($entity->getSocialStatus());
        $dto->setMaritalStatus($entity->getSocialStatus());

        return $dto;
    }


    /**
     * @param ProfileDto $dto
     *
     * @return Profile|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $entity = new Profile();

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setBirthDate($dto->getBirthDate());
        $entity->setDescription($dto->getDescription());
        $entity->setGender($dto->getGender());
        $entity->setPhoneNumber($dto->getPhoneNumber());
        $entity->setSmoker($dto->isSmoker());
        $entity->setHasJob($dto->hasJob());
        $entity->setHouseProud($dto->isHouseProud());
        $entity->setCook($dto->isCook());
        $entity->setDiet($dto->getDiet());
        $entity->setSocialStatus($dto->getSocialStatus());
        $entity->setMaritalStatus($dto->getSocialStatus());

        return $entity;
    }

}