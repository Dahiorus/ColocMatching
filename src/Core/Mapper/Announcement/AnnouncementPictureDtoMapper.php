<?php

namespace App\Core\Mapper\Announcement;

use App\Core\DTO\Announcement\AnnouncementPictureDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\AnnouncementPicture;
use App\Core\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class AnnouncementPictureDtoMapper implements DtoMapperInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @param AnnouncementPicture $entity The entity to transform
     *
     * @return AnnouncementPictureDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new AnnouncementPictureDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setWebPath($entity->getWebPath());
        $dto->setName($entity->getName());
        $dto->setFile($entity->getFile());
        $dto->setAnnouncementId($entity->getAnnouncement()->getId());

        return $dto;
    }


    /**
     * Transforms a profile picture DTO to a profile picture entity
     *
     * @param AnnouncementPictureDto $dto The DTO to transform
     *
     * @return AnnouncementPicture|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $announcement = $this->entityManager->find(Announcement::class, $dto->getAnnouncementId());
        $entity = new AnnouncementPicture($announcement, $dto->getFile());

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setName($dto->getName());
        $entity->setFile($dto->getFile());

        return $entity;
    }
}