<?php

namespace App\Core\Mapper\Group;

use App\Core\DTO\Group\GroupPictureDto;
use App\Core\Entity\Group\GroupPicture;
use App\Core\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Asset\Packages;

class GroupPictureDtoMapper implements DtoMapperInterface
{
    /** @var Packages */
    private $packages;

    /** @var EntityManagerInterface */
    private $entityManager;


    public function __construct(Packages $packages, EntityManagerInterface $entityManager)
    {
        $this->packages = $packages;
        $this->entityManager = $entityManager;
    }


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
        $dto->setWebPath($this->packages->getUrl($entity->getWebPath(), "group_pictures"));
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

        $id = $dto->getId();
        $entity = empty($id) ? new GroupPicture($dto->getFile())
            : $this->entityManager->find(GroupPicture::class, $id);

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setName($dto->getName());
        $entity->setFile($dto->getFile());

        return $entity;
    }
}
