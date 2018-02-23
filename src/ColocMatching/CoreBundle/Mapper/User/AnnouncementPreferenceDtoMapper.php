<?php

namespace ColocMatching\CoreBundle\Mapper\User;

use ColocMatching\CoreBundle\DTO\User\AnnouncementPreferenceDto;
use ColocMatching\CoreBundle\Entity\Announcement\Address;
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

    /**
     * Used by the address transformer
     * @var string
     */
    private $region;

    /**
     * Used by the address transformer
     * @var string
     */
    private $apiKey;


    public function __construct(EntityManagerInterface $entityManager, string $region, string $apiKey)
    {
        $this->entityManager = $entityManager;
        $this->region = $region;
        $this->apiKey = $apiKey;
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

        if (!empty($entity->getAddress()))
        {
            $dto->setAddressId($entity->getAddress()->getId());
            $dto->setAddress($entity->getAddress()->getFormattedAddress());
        }

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

        if (!empty($dto->getAddress()))
        {
            $entity->setAddress($this->getAddressFromDto($dto));
        }

        return $entity;
    }


    /**
     * Converts the address string value to the address entity value
     *
     * @param AnnouncementPreferenceDto $dto The DTO containing the address value
     *
     * @return Address
     */
    private function getAddressFromDto(AnnouncementPreferenceDto $dto)
    {
        $addressId = $dto->getAddressId();
        $formattedAddress = $dto->getAddress();

        // FIXME pass it as a service
        $transformer = new AddressTypeToAddressTransformer($this->region, $this->apiKey);

        $address = $transformer->reverseTransform($formattedAddress);
        $address->setId($addressId);

        return $address;
    }
}