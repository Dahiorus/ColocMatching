<?php

namespace ColocMatching\CoreBundle\Mapper\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\HousingDto;
use ColocMatching\CoreBundle\Entity\Announcement\Housing;
use ColocMatching\CoreBundle\Mapper\DtoMapperInterface;

class HousingDtoMapper implements DtoMapperInterface
{
    /**
     * @param Housing $entity
     *
     * @return HousingDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new HousingDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setType($entity->getType());
        $dto->setRoomCount($entity->getRoomCount());
        $dto->setBedroomCount($entity->getBedroomCount());
        $dto->setBathroomCount($entity->getBathroomCount());
        $dto->setSurfaceArea($entity->getSurfaceArea());
        $dto->setRoomMateCount($entity->getRoomMateCount());

        return $dto;
    }


    /**
     * @param HousingDto $dto
     *
     * @return Housing|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $entity = new Housing();

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setType($dto->getType());
        $entity->setRoomCount($dto->getRoomCount());
        $entity->setBedroomCount($dto->getBedroomCount());
        $entity->setBathroomCount($dto->getBathroomCount());
        $entity->setSurfaceArea($dto->getSurfaceArea());
        $entity->setRoomMateCount($dto->getRoomMateCount());

        return $entity;
    }
}