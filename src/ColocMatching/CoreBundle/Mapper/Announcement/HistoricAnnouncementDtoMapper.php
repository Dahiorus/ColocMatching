<?php

namespace ColocMatching\CoreBundle\Mapper\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\HistoricAnnouncementDto;
use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Form\DataTransformer\AddressTypeToAddressTransformer;
use ColocMatching\CoreBundle\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class HistoricAnnouncementDtoMapper implements DtoMapperInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var AddressTypeToAddressTransformer
     */
    private $addressTransformer;


    public function __construct(EntityManagerInterface $entityManager,
        AddressTypeToAddressTransformer $addressTransformer)
    {
        $this->entityManager = $entityManager;
        $this->addressTransformer = $addressTransformer;
    }


    /**
     * @param HistoricAnnouncement $entity
     *
     * @return HistoricAnnouncementDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new HistoricAnnouncementDto();

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
        $dto->setCreationDate($entity->getCreationDate());

        return $dto;
    }


    /**
     * @param HistoricAnnouncementDto $dto
     *
     * @return HistoricAnnouncement|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $creator = $this->entityManager->find(User::class, $dto->getCreatorId());
        $entity = new HistoricAnnouncement($creator);

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setTitle($dto->getTitle());
        $entity->setType($dto->getType());
        $entity->setRentPrice($dto->getRentPrice());
        $entity->setStartDate($dto->getStartDate());
        $entity->setEndDate($dto->getEndDate());
        $entity->setLocation($this->addressTransformer->reverseTransform($dto->getLocation()));
        $entity->setCreationDate($dto->getCreationDate());

        return $entity;
    }
}