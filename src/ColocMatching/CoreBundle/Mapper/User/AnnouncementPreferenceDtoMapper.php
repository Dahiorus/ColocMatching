<?php

namespace ColocMatching\CoreBundle\Mapper\User;

use ColocMatching\CoreBundle\DTO\User\AnnouncementPreferenceDto;
use ColocMatching\CoreBundle\Entity\User\AnnouncementPreference;
use ColocMatching\CoreBundle\Form\DataTransformer\AddressTypeToAddressTransformer;
use ColocMatching\CoreBundle\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class AnnouncementPreferenceDtoMapper implements DtoMapperInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /** @var AddressTypeToAddressTransformer */
    private $addressTransformer;


    public function __construct(EntityManagerInterface $entityManager,
        AddressTypeToAddressTransformer $addressTransformer)
    {
        $this->entityManager = $entityManager;
        $this->addressTransformer = $addressTransformer;
    }


    /**
     * @param AnnouncementPreference $entity
     *
     * @return AnnouncementPreferenceDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new AnnouncementPreferenceDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setStartDateAfter($entity->getStartDateAfter());
        $dto->setStartDateBefore($entity->getStartDateBefore());
        $dto->setEndDateAfter($entity->getEndDateAfter());
        $dto->setEndDateBefore($entity->getEndDateBefore());
        $dto->setRentPriceStart($entity->getRentPriceStart());
        $dto->setRentPriceEnd($entity->getRentPriceEnd());
        $dto->setWithPictures($entity->withPictures());
        $dto->setAddress($this->addressTransformer->transform($entity->getAddress()));

        return $dto;
    }


    /**
     * @param AnnouncementPreferenceDto $dto
     *
     * @return AnnouncementPreference|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $entity = new AnnouncementPreference();

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setStartDateAfter($dto->getStartDateAfter());
        $entity->setStartDateBefore($dto->getStartDateBefore());
        $entity->setEndDateAfter($dto->getEndDateAfter());
        $entity->setEndDateBefore($dto->getEndDateBefore());
        $entity->setRentPriceStart($dto->getRentPriceStart());
        $entity->setRentPriceEnd($dto->getRentPriceEnd());
        $entity->setWithPictures($dto->withPictures());
        $entity->setAddress($this->addressTransformer->reverseTransform($dto->getAddress()));

        return $entity;
    }

}
