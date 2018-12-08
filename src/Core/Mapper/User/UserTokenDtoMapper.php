<?php

namespace App\Core\Mapper\User;

use App\Core\DTO\User\UserTokenDto;
use App\Core\Entity\User\UserToken;

class UserTokenDtoMapper
{
    /**
     * @param UserToken $entity
     *
     * @return UserTokenDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new UserTokenDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setToken($entity->getToken());
        $dto->setUsername($entity->getUsername());
        $dto->setReason($entity->getReason());
        $dto->setExpirationDate($entity->getExpirationDate());

        return $dto;
    }


    /**
     * @param UserTokenDto $dto
     *
     * @return UserToken|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $entity = new UserToken($dto->getToken(), $dto->getUsername(), $dto->getToken(), $dto->getExpirationDate());

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());

        return $entity;
    }
}