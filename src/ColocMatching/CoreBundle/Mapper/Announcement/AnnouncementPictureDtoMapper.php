<?php

namespace ColocMatching\CoreBundle\Mapper\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;
use ColocMatching\CoreBundle\Form\Type\Announcement\AnnouncementPictureDto;
use ColocMatching\CoreBundle\Mapper\DtoMapperInterface;
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
        $entity->setName($dto->getName());
        $entity->setFile($dto->getFile());

        return $entity;
    }
}