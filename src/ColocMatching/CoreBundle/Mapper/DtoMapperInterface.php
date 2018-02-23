<?php

namespace ColocMatching\CoreBundle\Mapper;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\Entity\EntityInterface;

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