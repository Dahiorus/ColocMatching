<?php

namespace App\Core\Mapper\User;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Group\Group;
use App\Core\Entity\User\AnnouncementPreference;
use App\Core\Entity\User\User;
use App\Core\Entity\User\UserPreference;
use App\Core\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class UserDtoMapper implements DtoMapperInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ProfilePictureDtoMapper */
    private $profilePictureDtoMapper;


    public function __construct(EntityManagerInterface $entityManager, ProfilePictureDtoMapper $profilePictureDtoMapper)
    {
        $this->entityManager = $entityManager;
        $this->profilePictureDtoMapper = $profilePictureDtoMapper;
    }


    /**
     * Transforms a user entity to a user DTO
     *
     * @param User $entity The entity to transform
     *
     * @return UserDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        /** @var UserDto $dto */
        $dto = new UserDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setEmail($entity->getEmail());
        $dto->setPassword($entity->getPassword());
        $dto->setFirstName($entity->getFirstName());
        $dto->setLastName($entity->getLastName());
        $dto->setStatus($entity->getStatus());
        $dto->setRoles($entity->getRoles());
        $dto->setType($entity->getType());
        $dto->setLastLogin($entity->getLastLogin());
        $dto->setPicture($this->profilePictureDtoMapper->toDto($entity->getPicture()));
        $dto->setGender($entity->getGender());
        $dto->setBirthDate($entity->getBirthDate());
        $dto->setDescription($entity->getDescription());
        $dto->setPhoneNumber($entity->getPhoneNumber());

        if ($entity->hasAnnouncement())
        {
            $dto->setAnnouncementId($entity->getAnnouncement()->getId());
        }

        if ($entity->hasGroup())
        {
            $dto->setGroupId($entity->getGroup()->getId());
        }

        $dto->setUserPreferenceId($entity->getUserPreference()->getId());
        $dto->setAnnouncementPreferenceId($entity->getAnnouncementPreference()->getId());

        return $dto;
    }


    /**
     * Transforms a user DTO to a user entity
     *
     * @param UserDto $dto The DTO to transform
     *
     * @return User|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        /** @var User $entity */
        $entity = new User($dto->getEmail(), $dto->getPlainPassword(), $dto->getFirstName(), $dto->getLastName());

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setPassword($dto->getPassword());
        $entity->setRoles($dto->getRoles());
        $entity->setType($dto->getType());
        $entity->setLastLogin($dto->getLastLogin());
        $entity->setStatus($dto->getStatus());
        $entity->setPicture($this->profilePictureDtoMapper->toEntity($dto->getPicture()));
        $entity->setBirthDate($dto->getBirthDate());
        $entity->setDescription($dto->getDescription());
        $entity->setGender($dto->getGender());
        $entity->setPhoneNumber($dto->getPhoneNumber());

        if (!empty($dto->getAnnouncementId()))
        {
            $announcement = $this->entityManager->find(Announcement::class, $dto->getAnnouncementId());
            $entity->setAnnouncement($announcement);
        }

        if (!empty($dto->getGroupId()))
        {
            $group = $this->entityManager->find(Group::class, $dto->getGroupId());
            $entity->setGroup($group);
        }

        if (!empty($dto->getUserPreferenceId()))
        {
            $userPreference = $this->entityManager->find(UserPreference::class, $dto->getUserPreferenceId());
            $entity->setUserPreference($userPreference);
        }

        if (!empty($dto->getAnnouncementPreferenceId()))
        {
            $announcementPreference = $this->entityManager->find(AnnouncementPreference::class,
                $dto->getAnnouncementPreferenceId());
            $entity->setAnnouncementPreference($announcementPreference);
        }

        return $entity;
    }

}
