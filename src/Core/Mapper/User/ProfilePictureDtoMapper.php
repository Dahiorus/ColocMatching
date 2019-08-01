<?php

namespace App\Core\Mapper\User;

use App\Core\DTO\User\ProfilePictureDto;
use App\Core\Entity\User\ProfilePicture;
use App\Core\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Asset\Packages;

class ProfilePictureDtoMapper implements DtoMapperInterface
{
    /** @var Packages */
    private $assets;

    /** @var EntityManagerInterface */
    private $entityManager;


    public function __construct(Packages $packages, EntityManagerInterface $entityManager)
    {
        $this->assets = $packages;
        $this->entityManager = $entityManager;
    }


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
        $dto->setWebPath($this->assets->getUrl($entity->getWebPath()));
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

        $id = $dto->getId();
        $entity = empty($id) ? new ProfilePicture($dto->getFile())
            : $this->entityManager->find(ProfilePicture::class, $id);

        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setName($dto->getName());
        $entity->setFile($dto->getFile());

        return $entity;
    }

}
