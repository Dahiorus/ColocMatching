<?php

namespace App\Core\Mapper\Group;

use App\Core\DTO\Group\GroupPictureDto;
use App\Core\Entity\Group\GroupPicture;
use App\Core\Mapper\DtoMapperInterface;

class GroupPictureDtoMapper implements DtoMapperInterface
{
    /**
     * @param GroupPicture $entity The entity to transform
     *
     * @return GroupPictureDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new GroupPictureDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setWebPath($entity->getWebPath());
        $dto->setName($entity->getName());
        $dto->setFile($entity->getFile());

        return $dto;
    }


    /**
     * @param GroupPictureDto $dto The DTO to transform
     *
     * @return GroupPicture|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $entity = new GroupPicture($dto->getFile());

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setName($dto->getName());
        $entity->setFile($dto->getFile());

        return $entity;
    }
}