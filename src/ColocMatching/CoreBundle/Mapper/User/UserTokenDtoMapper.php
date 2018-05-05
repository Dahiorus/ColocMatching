<?php

namespace ColocMatching\CoreBundle\Mapper\User;

use ColocMatching\CoreBundle\DTO\User\UserTokenDto;
use ColocMatching\CoreBundle\Entity\User\UserToken;

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

        $entity = new UserToken($dto->getToken(), $dto->getUsername(), $dto->getToken());

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());

        return $entity;
    }
}