<?php

namespace App\Core\Mapper\Announcement;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\Announcement\AnnouncementPictureDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\AnnouncementPicture;
use App\Core\Entity\Announcement\Housing;
use App\Core\Entity\User\User;
use App\Core\Form\DataTransformer\StringToAddressTransformer;
use App\Core\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class AnnouncementDtoMapper implements DtoMapperInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var StringToAddressTransformer */
    private $addressTransformer;

    /** @var AnnouncementPictureDtoMapper */
    private $pictureDtoMapper;


    public function __construct(EntityManagerInterface $entityManager,
        StringToAddressTransformer $addressTransformer, AnnouncementPictureDtoMapper $pictureDtoMapper)
    {
        $this->entityManager = $entityManager;
        $this->addressTransformer = $addressTransformer;
        $this->pictureDtoMapper = $pictureDtoMapper;
    }


    /**
     * @param Announcement $entity
     *
     * @return AnnouncementDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new AnnouncementDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setTitle($entity->getTitle());
        $dto->setType($entity->getType());
        $dto->setCreatorId($entity->getCreator()->getId());
        $dto->setRentPrice($entity->getRentPrice());
        $dto->setStartDate($entity->getStartDate());
        $dto->setEndDate($entity->getEndDate());
        $dto->setLocation($this->addressTransformer->transform($entity->getLocation()));
        $dto->setDescription($entity->getDescription());
        $dto->setStatus($entity->getStatus());
        $dto->setShortLocation($entity->getLocation()->getShortAddress());
        $dto->setHousingId($entity->getHousing()->getId());
        $dto->setPictures($entity->getPictures()->map(function (AnnouncementPicture $picture) {
            return $this->pictureDtoMapper->toDto($picture);
        }));

        return $dto;
    }


    /**
     * @param AnnouncementDto $dto
     *
     * @return Announcement|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $creator = $this->entityManager->find(User::class, $dto->getCreatorId());
        $entity = new Announcement($creator);

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setTitle($dto->getTitle());
        $entity->setType($dto->getType());
        $entity->setRentPrice($dto->getRentPrice());
        $entity->setStartDate($dto->getStartDate());
        $entity->setEndDate($dto->getEndDate());
        $entity->setLocation($this->addressTransformer->reverseTransform($dto->getLocation()));
        $entity->setDescription($dto->getDescription());
        $entity->setStatus($dto->getStatus());
        $entity->setPictures($dto->getPictures()->map(function (AnnouncementPictureDto $picture) {
            return empty($picture->getId()) ? $this->pictureDtoMapper->toEntity($picture)
                : $this->entityManager->find(AnnouncementPicture::class, $picture->getId());
        }));

        if (!empty($dto->getHousingId()))
        {
            $housing = $this->entityManager->find(Housing::class, $dto->getHousingId());
            $entity->setHousing($housing);
        }

        return $entity;
    }

}
