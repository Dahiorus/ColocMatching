<?php

namespace App\Core\Mapper\User;

use App\Core\DTO\User\ProfilePictureDto;
use App\Core\Entity\User\ProfilePicture;
use App\Core\Mapper\DtoMapperInterface;

class ProfilePictureDtoMapper implements DtoMapperInterface
{
    /**
     * Transforms a profile picture entity to a profile picture DTO
     *
     * @param ProfilePicture $entity The entity to transform
     *
     * @return ProfilePictureDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new ProfilePictureDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setWebPath($entity->getWebPath());
        $dto->setName($entity->getName());
        $dto->setFile($entity->getFile());

        return $dto;
    }


    /**
     * Transforms a profile picture DTO to a profile picture entity
     *
     * @param ProfilePictureDto $dto The DTO to transform
     *
     * @return ProfilePicture|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $entity = new ProfilePicture($dto->getFile());

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setName($dto->getName());
        $entity->setFile($dto->getFile());

        return $entity;
    }

}
