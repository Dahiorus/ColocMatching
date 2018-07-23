<?php

namespace ColocMatching\CoreBundle\Manager\User;

use ColocMatching\CoreBundle\DTO\User\AnnouncementPreferenceDto;
use ColocMatching\CoreBundle\DTO\User\ProfileDto;
use ColocMatching\CoreBundle\DTO\User\ProfilePictureDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\User\UserPreferenceDto;
use ColocMatching\CoreBundle\Entity\User\AnnouncementPreference;
use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Entity\User\UserPreference;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Form\Type\Security\EditPasswordForm;
use ColocMatching\CoreBundle\Form\Type\User\AnnouncementPreferenceDtoForm;
use ColocMatching\CoreBundle\Form\Type\User\ProfileDtoForm;
use ColocMatching\CoreBundle\Form\Type\User\RegistrationForm;
use ColocMatching\CoreBundle\Form\Type\User\UserDtoForm;
use ColocMatching\CoreBundle\Form\Type\User\UserPreferenceDtoForm;
use ColocMatching\CoreBundle\Manager\AbstractDtoManager;
use ColocMatching\CoreBundle\Mapper\User\AnnouncementPreferenceDtoMapper;
use ColocMatching\CoreBundle\Mapper\User\ProfileDtoMapper;
use ColocMatching\CoreBundle\Mapper\User\ProfilePictureDtoMapper;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Mapper\User\UserPreferenceDtoMapper;
use ColocMatching\CoreBundle\Security\User\EditPassword;
use ColocMatching\CoreBundle\Service\UserStatusHandler;
use ColocMatching\CoreBundle\Validator\FormValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Model manager of UserDto
 *
 * @author Dahiorus
 */
class UserDtoManager extends AbstractDtoManager implements UserDtoManagerInterface
{
    /** @var UserDtoMapper */
    protected $dtoMapper;

    /** @var FormValidator */
    private $formValidator;

    /** @var ProfilePictureDtoMapper */
    private $pictureDtoMapper;

    /** @var ProfileDtoMapper */
    private $profileDtoMapper;

    /** @var AnnouncementPreferenceDtoMapper */
    private $announcementPreferenceDtoMapper;

    /** @var UserPreferenceDtoMapper */
    private $userPreferenceDtoMapper;

    /** @var UserStatusHandler */
    private $userStatusHandler;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, UserDtoMapper $dtoMapper,
        FormValidator $formValidator, ProfilePictureDtoMapper $pictureDtoMapper, ProfileDtoMapper $profileDtoMapper,
        AnnouncementPreferenceDtoMapper $announcementPreferenceDtoMapper,
        UserPreferenceDtoMapper $userPreferenceDtoMapper, UserStatusHandler $userStatusHandler)
    {
        parent::__construct($logger, $em, $dtoMapper);

        $this->formValidator = $formValidator;
        $this->pictureDtoMapper = $pictureDtoMapper;
        $this->profileDtoMapper = $profileDtoMapper;
        $this->announcementPreferenceDtoMapper = $announcementPreferenceDtoMapper;
        $this->userPreferenceDtoMapper = $userPreferenceDtoMapper;
        $this->userStatusHandler = $userStatusHandler;
    }


    protected function getDomainClass() : string
    {
        return User::class;
    }


    /**
     * @inheritdoc
     */
    public function findByUsername(string $username) : UserDto
    {
        $this->logger->debug("Getting an existing user by username", array ("username" => $username));

        /** @var User $user */
        $user = $this->repository->findOneBy(array ("email" => $username));

        if (empty($user))
        {
            throw new EntityNotFoundException($this->getDomainClass(), "username", $username);
        }

        $this->logger->info("User found", array ("user" => $user));

        return $this->dtoMapper->toDto($user);
    }


    /**
     * @inheritdoc
     */
    public function create(array $data, bool $flush = true) : UserDto
    {
        $this->logger->debug("Creating a new user", array ("flush" => $flush));

        /** @var UserDto $userDto */
        $userDto = $this->formValidator->validateDtoForm(new UserDto(), $data, RegistrationForm::class, true,
            array ("validation_groups" => array ("Create", "Default")));

        /** @var User $user */
        $user = $this->dtoMapper->toEntity($userDto);
        $this->em->persist($user);
        $this->flush($flush);

        $this->logger->info("User created", array ("user" => $user));

        return $this->dtoMapper->toDto($user);
    }


    /**
     * @inheritdoc
     */
    public function update(UserDto $user, array $data, bool $clearMissing, bool $flush = true) : UserDto
    {
        $this->logger->debug("Updating an existing user",
            array ("user" => $user, "data" => $data, "clearMissing" => $clearMissing, "flush" => $flush));

        /** @var UserDto $userDto */
        $userDto = $this->formValidator->validateDtoForm($user, $data, UserDtoForm::class, $clearMissing);

        // we must force the update on the password
        if (!empty($userDto->getPlainPassword()))
        {
            $userDto->setPassword(null);
        }

        /** @var User $updatedUser */
        $updatedUser = $this->em->merge($this->dtoMapper->toEntity($userDto));
        $this->flush($flush);

        $this->logger->info("User updated", array ("user" => $updatedUser));

        return $this->dtoMapper->toDto($updatedUser);
    }


    /**
     * @inheritdoc
     */
    public function updatePassword(UserDto $user, array $data, bool $flush = true) : UserDto
    {
        $this->logger->debug("Updating the password of a user", array ("user" => $user));

        /** @var User $userEntity */
        $userEntity = $this->dtoMapper->toEntity($user);

        /** @var EditPassword $editPassword */
        $editPassword = $this->formValidator->validateForm(
            new EditPassword($userEntity), $data, EditPasswordForm::class, true);

        $userEntity->setPlainPassword($editPassword->getNewPassword());
        $userEntity->setPassword(null);

        $userEntity = $this->em->merge($userEntity);
        $this->flush($flush);

        return $this->dtoMapper->toDto($userEntity);
    }


    /**
     * @inheritdoc
     */
    public function updateStatus(UserDto $user, string $status, bool $flush = true) : UserDto
    {
        $this->logger->debug("Updating the status of a user", array ("user" => $user, "status" => $status));

        if ($user->getStatus() == $status)
        {
            $this->logger->debug("The user has already the status", array ("status" => $status));

            return $user;
        }

        /** @var User $userEntity */
        $userEntity = $this->dtoMapper->toEntity($user);

        switch ($status)
        {
            case UserConstants::STATUS_ENABLED:
                $userEntity = $this->userStatusHandler->enable($userEntity, $flush);
                break;
            case UserConstants::STATUS_VACATION:
                $userEntity = $this->userStatusHandler->disable($userEntity, $flush);
                break;
            case UserConstants::STATUS_BANNED:
                $userEntity = $this->userStatusHandler->ban($userEntity, $flush);
                break;
            default:
                throw new InvalidParameterException("status", "Unknown status '$status'");
        }

        $this->logger->info("User status updated", array ("user" => $userEntity));

        return $this->dtoMapper->toDto($userEntity);
    }


    /**
     * @inheritdoc
     */
    public function uploadProfilePicture(UserDto $user, File $file, bool $flush = true) : ProfilePictureDto
    {
        $this->logger->debug("Uploading a profile picture for a user",
            array ("user" => $user, "file" => $file, "flush" => $flush));

        /** @var ProfilePictureDto $pictureDto */
        $pictureDto = $this->formValidator->validatePictureDtoForm(
            empty($user->getPicture()) ? new ProfilePictureDto() : $user->getPicture(),
            $file, ProfilePictureDto::class);

        /** @var ProfilePicture $picture */
        $picture = $this->pictureDtoMapper->toEntity($pictureDto);
        /** @var User $entity */
        $entity = $this->dtoMapper->toEntity($user);
        $entity->setPicture($picture);

        if (empty($picture->getId()))
        {
            $this->em->persist($picture);
        }
        else
        {
            $picture = $this->em->merge($picture);
        }

        $this->em->merge($entity);
        $this->flush($flush);

        $this->logger->info("Profile picture uploaded", array ("picture" => $picture));

        return $this->pictureDtoMapper->toDto($picture);
    }


    /**
     * @inheritdoc
     */
    public function deleteProfilePicture(UserDto $user, bool $flush = true) : void
    {
        $this->logger->debug("Deleting a user's profile picture", array ("user" => $user));

        /** @var User $entity */
        $entity = $this->dtoMapper->toEntity($user);

        if (empty($entity->getPicture()))
        {
            $this->logger->warning("Trying to delete a non existing profile picture", array ("user" => $user));

            return;
        }

        /** @var ProfilePicture $picture */
        $picture = $this->em->find(ProfilePicture::class, $entity->getPicture()->getId());

        $this->logger->debug("Profile picture exists for the user", array ("user" => $user, "picture" => $picture));

        $entity->setPicture(null);

        $this->em->remove($picture);
        $this->em->merge($entity);
        $this->flush($flush);

        $this->logger->debug("Profile picture deleted");
    }


    /**
     * @inheritdoc
     */
    public function getProfile(UserDto $user) : ProfileDto
    {
        $this->logger->debug("Getting a user's profile", array ("user" => $user));

        $entity = $this->dtoMapper->toEntity($user);

        return $this->profileDtoMapper->toDto($entity->getProfile());
    }


    /**
     * @inheritdoc
     */
    public function updateProfile(UserDto $user, array $data, bool $clearMissing, bool $flush = true) : ProfileDto
    {
        $this->logger->debug("Updating a user's profile",
            array ("user" => $user, "data" => $data, "clearMissing" => $clearMissing, "flush" => $flush));

        /** @var ProfileDto $profile */
        $profile = $this->formValidator->validateDtoForm($this->getProfile($user), $data, ProfileDtoForm::class,
            $clearMissing);
        /** @var Profile $entity */
        $entity = $this->em->merge($this->profileDtoMapper->toEntity($profile));
        $this->flush($flush);

        $this->logger->info("User profile updated", array ("profile" => $entity));

        return $this->profileDtoMapper->toDto($entity);
    }


    /**
     * @inheritdoc
     */
    public function getAnnouncementPreference(UserDto $user) : AnnouncementPreferenceDto
    {
        $this->logger->debug("Getting a user's announcement search preferences", array ("user" => $user));

        $entity = $this->dtoMapper->toEntity($user);

        return $this->announcementPreferenceDtoMapper->toDto($entity->getAnnouncementPreference());
    }


    /**
     * @inheritdoc
     */
    public function updateAnnouncementPreference(UserDto $user, array $data,
        bool $clearMissing, bool $flush = true) : AnnouncementPreferenceDto
    {
        $this->logger->debug("Updating a user's announcement search preferences",
            array ("user" => $user, "data" => $data, "clearMissing" => $clearMissing, "flush" => $flush));

        /** @var AnnouncementPreferenceDto $preference */
        $preference = $this->formValidator->validateDtoForm($this->getAnnouncementPreference($user),
            $data, AnnouncementPreferenceDtoForm::class, $clearMissing);
        /** @var AnnouncementPreference $entity */
        $entity = $this->em->merge($this->announcementPreferenceDtoMapper->toEntity($preference));
        $this->flush($flush);

        $this->logger->info("User announcement preference updated", array ("preference" => $entity));

        return $this->announcementPreferenceDtoMapper->toDto($entity);
    }


    /**
     * @inheritdoc
     */
    public function getUserPreference(UserDto $user) : UserPreferenceDto
    {
        $this->logger->debug("Getting a user's user search preferences", array ("user" => $user));

        $entity = $this->dtoMapper->toEntity($user);

        return $this->userPreferenceDtoMapper->toDto($entity->getUserPreference());
    }


    /**
     * @inheritdoc
     */
    public function updateUserPreference(UserDto $user, array $data, bool $clearMissing,
        bool $flush = true) : UserPreferenceDto
    {
        $this->logger->debug("Updating a user's user search preferences",
            array ("user" => $user, "data" => $data, "clearMissing" => $clearMissing, "flush" => $flush));

        /** @var UserPreferenceDto $preference */
        $preference = $this->formValidator->validateDtoForm($this->getUserPreference($user),
            $data, UserPreferenceDtoForm::class, $clearMissing);
        /** @var UserPreference $entity */
        $entity = $this->em->merge($this->userPreferenceDtoMapper->toEntity($preference));
        $this->flush($flush);

        $this->logger->info("User profile preference updated", array ("preference" => $entity));

        return $this->userPreferenceDtoMapper->toDto($entity);
    }


    /**
     * @inheritdoc
     */
    public function addRole(UserDto $user, string $role, bool $flush = true) : UserDto
    {
        $this->logger->debug("Adding a role to a user", array ("user" => $user, "role" => $role, "flush" => $flush));

        /** @var User $entity */
        $entity = $this->repository->find($user->getId());
        $entity->addRole($role);

        $entity = $this->em->merge($entity);
        $this->flush($flush);

        $this->logger->info("User role added", array ("user" => $entity));

        return $this->dtoMapper->toDto($entity);
    }

}
