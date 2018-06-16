<?php

namespace ColocMatching\CoreBundle\Mapper\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\AnnouncementPreference;
use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserPreference;
use ColocMatching\CoreBundle\Mapper\DtoMapperInterface;
use ColocMatching\CoreBundle\Service\RoleService;
use Doctrine\ORM\EntityManagerInterface;

class UserDtoMapper implements DtoMapperInterface
{
    private const ROLE_ADMIN = "ROLE_ADMIN";

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ProfilePictureDtoMapper */
    private $profilePictureDtoMapper;

    /** @var RoleService */
    private $roleService;


    public function __construct(EntityManagerInterface $entityManager, ProfilePictureDtoMapper $profilePictureDtoMapper,
        RoleService $roleService)
    {
        $this->entityManager = $entityManager;
        $this->profilePictureDtoMapper = $profilePictureDtoMapper;
        $this->roleService = $roleService;
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
        $dto->setType($entity->getType());
        $dto->setLastLogin($entity->getLastLogin());
        $dto->setPicture($this->profilePictureDtoMapper->toDto($entity->getPicture()));
        $dto->setAdmin($this->roleService->isGranted(self::ROLE_ADMIN, $entity));

        if ($entity->hasAnnouncement())
        {
            $dto->setAnnouncementId($entity->getAnnouncement()->getId());
        }

        if ($entity->hasGroup())
        {
            $dto->setGroupId($entity->getGroup()->getId());
        }

        $dto->setProfileId($entity->getProfile()->getId());
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
        $entity->setType($dto->getType());
        $entity->setLastLogin($dto->getLastLogin());
        $entity->setStatus($dto->getStatus());
        $entity->setPicture($this->profilePictureDtoMapper->toEntity($dto->getPicture()));

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

        if (!empty($dto->getProfileId()))
        {
            $profile = $this->entityManager->find(Profile::class, $dto->getProfileId());
            $entity->setProfile($profile);
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

        if ($dto->isAdmin())
        {
            $entity->addRole(self::ROLE_ADMIN);
        }

        return $entity;
    }
}
