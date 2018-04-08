<?php

namespace ColocMatching\CoreBundle\Manager\User;

use ColocMatching\CoreBundle\DTO\User\AnnouncementPreferenceDto;
use ColocMatching\CoreBundle\DTO\User\ProfileDto;
use ColocMatching\CoreBundle\DTO\User\ProfilePictureDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\User\UserPreferenceDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\AnnouncementPreference;
use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Entity\User\UserPreference;
use ColocMatching\CoreBundle\Event\DeleteAnnouncementEvent;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidCredentialsException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Form\Type\Security\LoginForm;
use ColocMatching\CoreBundle\Form\Type\User\AnnouncementPreferenceDtoForm;
use ColocMatching\CoreBundle\Form\Type\User\EditPasswordType;
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
use ColocMatching\CoreBundle\Validator\FormValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, UserDtoMapper $dtoMapper,
        FormValidator $formValidator, UserPasswordEncoderInterface $passwordEncoder,
        ProfilePictureDtoMapper $pictureDtoMapper, ProfileDtoMapper $profileDtoMapper,
        AnnouncementPreferenceDtoMapper $announcementPreferenceDtoMapper,
        UserPreferenceDtoMapper $userPreferenceDtoMapper, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($logger, $em, $dtoMapper);

        $this->formValidator = $formValidator;
        $this->pictureDtoMapper = $pictureDtoMapper;
        $this->profileDtoMapper = $profileDtoMapper;
        $this->announcementPreferenceDtoMapper = $announcementPreferenceDtoMapper;
        $this->userPreferenceDtoMapper = $userPreferenceDtoMapper;
        $this->passwordEncoder = $passwordEncoder;
        $this->eventDispatcher = $eventDispatcher;
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

        return $this->dtoMapper->toDto($user);
    }


    /**
     * @inheritdoc
     */
    public function findByCredentials(string $_username, string $_rawPassword) : UserDto
    {
        $this->logger->debug("Getting a user by credentials", array ("username" => $_username));

        $data = array ("_username" => $_username, "_password" => $_rawPassword);
        $this->formValidator->validateForm(null, $data, LoginForm::class, true);

        /** @var User $user */
        $user = $this->repository->findOneBy(array ("email" => $_username));

        if (empty($user)
            || $user->getStatus() == UserConstants::STATUS_BANNED
            || !$this->passwordEncoder->isPasswordValid($user, $_rawPassword))
        {
            throw new InvalidCredentialsException();
        }

        $user->setLastLogin(new \DateTime());

        $user = $this->em->merge($user);
        $this->flush(true);

        return $this->dtoMapper->toDto($user);
    }


    /**
     * @inheritdoc
     */
    public function create(array $data, bool $flush = true) : UserDto
    {
        $this->logger->debug("Creating a new user", array ("data" => $data, "flush" => $flush));

        /** @var UserDto $userDto */
        $userDto = $this->formValidator->validateDtoForm(new UserDto(), $data, RegistrationForm::class, true,
            array ("validation_groups" => array ("Create", "Default")));

        /** @var User $user */
        $user = $this->dtoMapper->toEntity($userDto);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));

        $this->em->persist($user);
        $this->flush($flush);

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

        /** @var User $updatedUser */
        $updatedUser = $this->em->merge($this->dtoMapper->toEntity($userDto));
        $this->flush($flush);

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
            new EditPassword($userEntity), $data, EditPasswordType::class, true);

        // setting the new password
        $newPassword = $this->passwordEncoder->encodePassword($userEntity, $editPassword->getNewPassword());
        $userEntity->setPassword($newPassword);

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
                $userEntity = $this->enable($userEntity, $flush);
                break;
            case UserConstants::STATUS_VACATION:
                $userEntity = $this->disable($userEntity, $flush);
                break;
            case UserConstants::STATUS_BANNED:
                $userEntity = $this->ban($userEntity, $flush);
                break;
            default:
                throw new InvalidParameterException("status", "Unknown status '$status'");
        }

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

        return $this->userPreferenceDtoMapper->toDto($entity);
    }


    public function addRole(UserDto $user, string $role, bool $flush = true) : UserDto
    {
        $this->logger->debug("Adding a role to a user", array ("user" => $user, "role" => $role, "flush" => $flush));

        /** @var User $entity */
        $entity = $this->dtoMapper->toEntity($user);
        $entity->addRole($role);

        $entity = $this->em->merge($entity);
        $this->flush($flush);

        return $this->dtoMapper->toDto($entity);
    }


    /**
     * Bans a user and disables all stuffs related to this user
     *
     * @param User $user The user to ban
     * @param bool $flush If the operation is flushed
     *
     * @return User
     */
    private function ban(User $user, bool $flush) : User
    {
        $this->logger->debug("Banning a user", array ("user" => $user));

        if ($user->hasAnnouncement())
        {
            $this->logger->debug("Deleting the announcement of the user");

            $this->eventDispatcher->dispatch(DeleteAnnouncementEvent::DELETE_EVENT,
                new DeleteAnnouncementEvent($user->getAnnouncement()->getId()));
            $this->em->remove($user->getAnnouncement());
            $user->setAnnouncement(null);
        }
        else if ($user->hasGroup())
        {
            $this->logger->debug("Removing the user from his group");

            $group = $user->getGroup();
            $group->removeMember($user);
            $user->setGroup(null);

            if ($group->hasMembers())
            {
                $this->logger->debug("Setting the new creator of the group");

                /** @var User $newCreator */
                $newCreator = $group->getMembers()->first();
                $group->setCreator($newCreator);
                $newCreator->setGroup($group);
                $this->em->merge($group);
                $this->em->merge($newCreator);
            }
            else
            {
                $this->logger->debug("Deleting the group of the user");

                $this->em->remove($group);
            }
        }

        // TODO if the user type is search remove it from group/announcement invitees

        $user->setStatus(UserConstants::STATUS_BANNED);

        /** @var User $user */
        $user = $this->em->merge($user);
        $this->flush($flush);

        return $user;
    }


    /**
     * Sets the status of a user to "vacation"
     *
     * @param User $user The user to disable
     * @param bool $flush If the operation is flushed
     *
     * @return User
     */
    private function disable(User $user, bool $flush) : User
    {
        $this->logger->debug("Disabling a user", array ("user" => $user));

        $user->setStatus(UserConstants::STATUS_VACATION);

        if ($user->hasAnnouncement())
        {
            $this->logger->debug("Disabling the announcement of the user");

            $user->getAnnouncement()->setStatus(Announcement::STATUS_DISABLED);
            $this->em->merge($user->getAnnouncement());
        }

        if ($user->hasGroup())
        {
            $this->logger->debug("Closing the group of the user");

            $user->getGroup()->setStatus(Group::STATUS_CLOSED);
            $this->em->merge($user->getGroup());
        }

        /** @var User $user */
        $user = $this->em->merge($user);
        $this->flush($flush);

        return $user;
    }


    /**
     * Enables a user and changes the status to "enabled"
     *
     * @param User $user The user to enable
     * @param bool $flush If the operation is flushed
     *
     * @return User
     */
    private function enable(User $user, bool $flush) : User
    {
        $this->logger->debug("Enabling a user", array ("user" => $user));

        $user->setStatus(UserConstants::STATUS_ENABLED);

        if ($user->hasAnnouncement())
        {
            $this->logger->debug("Enabling the announcement of the user");

            $announcement = $user->getAnnouncement();
            $announcement->setStatus(Announcement::STATUS_ENABLED);
            $this->em->merge($announcement);
        }

        if ($user->hasGroup())
        {
            $this->logger->debug("Opening the group of the user");

            $group = $user->getGroup();
            $group->setStatus(Group::STATUS_OPENED);
            $this->em->merge($group);
        }

        /** @var User $user */
        $user = $this->em->merge($user);
        $this->flush($flush);

        return $user;
    }

}
