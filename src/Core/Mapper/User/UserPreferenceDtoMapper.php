<?php

namespace App\Core\Mapper\User;

use App\Core\DTO\User\UserPreferenceDto;
use App\Core\Entity\User\UserPreference;

class UserPreferenceDtoMapper
{
    /**
     * @param UserPreference $entity
     *
     * @return UserPreferenceDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new UserPreferenceDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setType($entity->getType());
        $dto->setGender($entity->getGender());
        $dto->setAgeStart($entity->getAgeStart());
        $dto->setAgeEnd($entity->getAgeEnd());
        $dto->setWithDescription($entity->withDescription());
        $dto->setSmoker($entity->isSmoker());
        $dto->setHasJob($entity->hasJob());
        $dto->setDiet($entity->getDiet());
        $dto->setSocialStatus($entity->getSocialStatus());
        $dto->setMaritalStatus($entity->getMaritalStatus());

        return $dto;
    }


    /**
     * @param UserPreferenceDto $dto
     *
     * @return UserPreference|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $entity = new UserPreference();

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setType($dto->getType());
        $entity->setGender($dto->getGender());
        $entity->setAgeStart($dto->getAgeStart());
        $entity->setAgeEnd($dto->getAgeEnd());
        $entity->setWithDescription($dto->withDescription());
        $entity->setSmoker($dto->isSmoker());
        $entity->setHasJob($dto->hasJob());
        $entity->setDiet($dto->getDiet());
        $entity->setSocialStatus($dto->getSocialStatus());
        $entity->setMaritalStatus($dto->getMaritalStatus());

        return $entity;
    }

}