<?php

namespace App\Core\Mapper;

use App\Core\DTO\AbstractDto;
use App\Core\Entity\EntityInterface;

interface DtoMapperInterface
{

    /**
     * Maps an entity to a DTO
     *
     * @param EntityInterface $entity The entity to transform
     *
     * @return AbstractDto|null
     */
    public function toDto($entity);


    /**
     * Maps a DTO to an entity
     *
     * @param AbstractDto $dto The DTO to transform
     *
     * @return EntityInterface|null
     */
    public function toEntity($dto);
}