<?php

namespace App\Core\Mapper\Announcement;

use App\Core\DTO\Announcement\AnnouncementPictureDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\AnnouncementPicture;
use App\Core\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Asset\Packages;

class AnnouncementPictureDtoMapper implements DtoMapperInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Packages */
    private $packages;


    public function __construct(EntityManagerInterface $entityManager, Packages $packages)
    {
        $this->entityManager = $entityManager;
        $this->packages = $packages;
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
        $dto->setWebPath($this->packages->getUrl($entity->getWebPath(), "announcement_pictures"));
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
