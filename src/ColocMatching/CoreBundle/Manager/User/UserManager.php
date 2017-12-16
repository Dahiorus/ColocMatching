<?php

namespace ColocMatching\CoreBundle\Manager\User;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\AnnouncementPreference;
use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Entity\User\UserPreference;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Form\Type\User\AnnouncementPreferenceType;
use ColocMatching\CoreBundle\Form\Type\User\EditPasswordType;
use ColocMatching\CoreBundle\Form\Type\User\ProfileType;
use ColocMatching\CoreBundle\Form\Type\User\RegistrationType;
use ColocMatching\CoreBundle\Form\Type\User\UserPreferenceType;
use ColocMatching\CoreBundle\Form\Type\User\UserType;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use ColocMatching\CoreBundle\Repository\User\UserRepository;
use ColocMatching\CoreBundle\Security\User\EditPassword;
use ColocMatching\CoreBundle\Validator\EntityValidator;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * CRUD Manager of the entity User
 *
 * @author brondon.ung
 */
class UserManager implements UserManagerInterface {

    /** @var ObjectManager */
    private $manager;

    /** @var UserRepository */
    private $repository;

    /** @var EntityValidator */
    private $entityValidator;

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    /** @var LoggerInterface */
    private $logger;


    public function __construct(ObjectManager $manager, string $entityClass, EntityValidator $entityValidator,
        UserPasswordEncoderInterface $passwordEncoder, LoggerInterface $logger) {
        $this->manager = $manager;
        $this->repository = $manager->getRepository($entityClass);
        $this->entityValidator = $entityValidator;
        $this->passwordEncoder = $passwordEncoder;
        $this->logger = $logger;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::list()
     */
    public function list(PageableFilter $filter, array $fields = null) : array {
        $this->logger->debug("Getting users with pagination", array ("filter" => $filter, "fields" => $fields));

        return $this->repository->findByPageable($filter, $fields);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countAll()
     */
    public function countAll() : int {
        $this->logger->debug("Counting all users");

        return $this->repository->count();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::findByUsername()
     */
    public function findByUsername(string $username) : User {
        $this->logger->debug("Getting an existing user by username", array ("username" => $username));

        /** @var User $user */
        $user = $this->repository->findOneBy(array ("email" => $username));

        if (empty($user)) {
            throw new UserNotFoundException("username", $username);
        }

        return $user;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::create()
     */
    public function create(array $data, bool $flush = true) : User {
        $this->logger->debug("Creating a new user", array ("data" => $data));

        /** @var User $user */
        $user = $this->entityValidator->validateEntityForm(new User(), $data, RegistrationType::class, true,
            array ("validation_groups" => array ("Create", "Default")));
        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));

        $this->manager->persist($user);

        if ($flush) {
            $this->manager->flush();
        }

        return $user;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::read()
     */
    public function read(int $id, array $fields = null) {
        $this->logger->debug("Getting an existing user", array ("id" => $id, "fields" => $fields));

        /** @var User */
        $user = $this->repository->findById($id, $fields);

        if (empty($user)) {
            throw new UserNotFoundException("id", $id);
        }

        return $user;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::update()
     */
    public function update(User $user, array $data, bool $clearMissing) : User {
        $this->logger->debug("Updating an existing user",
            array ("user" => $user, "data" => $data, "clearMissing" => $clearMissing));

        /** @var User $updatedUser */
        $updatedUser = $this->entityValidator->validateEntityForm($user, $data, UserType::class, $clearMissing);

        $this->manager->merge($updatedUser);
        $this->manager->flush();

        return $updatedUser;
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::delete()
     */
    public function delete(User $user) {
        $this->logger->debug("Deleting an existing user", array ("user" => $user));

        $this->manager->remove($user);
        $this->manager->flush();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::search()
     */
    public function search(UserFilter $filter, array $fields = null) : array {
        $this->logger->debug("Getting users by filtering", array ("filter" => $filter, "fields" => $fields));

        return $this->repository->findByFilter($filter, $fields);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countBy()
     */
    public function countBy(UserFilter $filter) : int {
        $this->logger->debug("Counting users by filtering", array ("filter" => $filter));

        return $this->repository->countByFilter($filter);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::uploadProfilePicture()
     */
    public function uploadProfilePicture(User $user, File $file) : ProfilePicture {
        /** @var ProfilePicture */
        $picture = empty($user->getPicture()) ? new ProfilePicture() : $user->getPicture();

        $this->logger->debug("Uploading a profile picture for an existing user",
            array ("user" => $user, "file" => $file));

        $uploadedPicture = $this->entityValidator->validatePictureForm($picture, $file, ProfilePicture::class);
        $user->setPicture($uploadedPicture);

        $this->manager->persist($user);
        $this->manager->flush();

        $this->logger->debug("Profile picture uploaded", array ("picture" => $uploadedPicture));

        return $user->getPicture();
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::deleteProfilePicture()
     */
    public function deleteProfilePicture(User $user) {
        $this->logger->debug("Deleting a user's profile picture", array ("user" => $user));

        /** @var ProfilePicture */
        $picture = $user->getPicture();

        if (!empty($picture)) {
            $this->logger->debug("Profile picture exists for the user", array ("user" => $user, "picture" => $picture));

            $this->manager->remove($picture);
            $this->manager->flush();

            $user->setPicture(null);
        }
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::updateProfile()
     */
    public function updateProfile(User $user, array $data, bool $clearMissing) : Profile {
        $this->logger->debug("Updating a user's profile",
            array ("user" => $user, "data" => $data, "clearMissing" => $clearMissing));

        /** @var Profile $profile */
        $profile = $this->entityValidator->validateEntityForm($user->getProfile(), $data, ProfileType::class,
            $clearMissing);

        $this->manager->merge($profile);
        $this->manager->flush();

        return $profile;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::updateAnnouncementPreference()
     */
    public function updateAnnouncementPreference(User $user, array $data, bool $clearMissing) : AnnouncementPreference {
        $this->logger->debug("Updating a user's announcement preference",
            array ("user" => $user, "data" => $data, "clearMissing" => $clearMissing));

        /** @var AnnouncementPreference $announcementPreference */
        $announcementPreference = $user->getAnnouncementPreference();
        /** @var AnnouncementPreference $preference */
        $preference = $this->entityValidator->validateEntityForm($announcementPreference, $data,
            AnnouncementPreferenceType::class, $clearMissing,
            array ("address_data" => $announcementPreference->getAddress()));

        $this->manager->merge($preference);
        $this->manager->flush();

        return $preference;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::updateUserPreference()
     */
    public function updateUserPreference(User $user, array $data, bool $clearMissing) : UserPreference {
        $this->logger->debug("Updating a user's profile preference",
            array ("user" => $user, "data" => $data, "clearMissing" => $clearMissing));

        /** @var UserPreference $preference */
        $preference = $this->entityValidator->validateEntityForm($user->getUserPreference(), $data,
            UserPreferenceType::class, $clearMissing);

        $this->manager->merge($preference);
        $this->manager->flush();

        return $preference;
    }


    /**
     * {@inheritDoc}
     * @see UserManagerInterface::updateStatus()
     */
    public function updateStatus(User $user, string $status) : User {
        $this->logger->debug("Updating the status of a user", array ("user" => $user, "status" => $status));

        if ($user->getStatus() == $status) {
            $this->logger->debug("The user has already the status", array ("status" => $status));

            return $user;
        }

        switch ($status) {
            case UserConstants::STATUS_ENABLED:
                $user = $this->enable($user);
                break;
            case UserConstants::STATUS_VACATION:
                $user = $this->disable($user);
                break;
            case UserConstants::STATUS_BANNED:
                $user = $this->ban($user);
                break;
            default:
                throw new InvalidParameterException("status", "Unknown status '$status'");
        }

        return $user;
    }


    /**
     * {@inheritDoc}
     * @see UserManagerInterface::updatePassword()
     */
    public function updatePassword(User $user, array $data, bool $flush = true) : User {
        $this->logger->debug("Updating the password of a user", array ("user" => $user));

        /** @var EditPassword $editPassword */
        $editPassword = $this->entityValidator->validateForm(
            new EditPassword($user), $data, EditPasswordType::class, true);

        // setting the new password
        $user->setPassword($this->passwordEncoder->encodePassword($user, $editPassword->getNewPassword()));

        $this->manager->merge($user);

        if ($flush) {
            $this->manager->flush();
        }

        return $user;
    }


    /**
     * Bans a user and disables all stuffs related to this user
     *
     * @param User $user The user to ban
     *
     * @return User
     */
    private function ban(User $user) : User {
        $this->logger->debug("Banning a user", array ("user" => $user));

        $user->setStatus(UserConstants::STATUS_BANNED);

        if ($user->hasAnnouncement()) {
            $this->logger->debug("Deleting the announcement of the user");

            $this->manager->remove($user->getAnnouncement());
            $user->setAnnouncement(null);
        }
        else if ($user->hasGroup()) {
            $this->logger->debug("Removing the user from his group");

            $group = $user->getGroup();
            $group->removeMember($user);

            if ($group->hasMembers()) {
                $group->setCreator($group->getMembers()->first());
                $this->manager->merge($group);
            }
            else {
                $this->logger->debug("Deleting the group of the user");

                $this->manager->remove($group);
            }

            $user->setGroup(null);
        }

        $this->manager->merge($user);
        $this->manager->flush();

        return $user;
    }


    /**
     * Sets the status of a user to "vacation"
     *
     * @param User $user The user to disable
     *
     * @return User
     */
    private function disable(User $user) : User {
        $this->logger->debug("Disabling a user", array ("user" => $user));

        $user->setStatus(UserConstants::STATUS_VACATION);

        if ($user->hasAnnouncement()) {
            $this->logger->debug("Disabling the announcement of the user");

            $user->getAnnouncement()->setStatus(Announcement::STATUS_DISABLED);
            $this->manager->merge($user->getAnnouncement());
        }

        if ($user->hasGroup()) {
            $this->logger->debug("Closing the group of the user");

            $user->getGroup()->setStatus(Group::STATUS_CLOSED);
            $this->manager->merge($user->getGroup());
        }

        $this->manager->merge($user);
        $this->manager->flush();

        return $user;
    }


    /**
     * Enables a user and changes the status to "enabled"
     *
     * @param User $user The user to enable
     *
     * @return User
     */
    private function enable(User $user) : User {
        $this->logger->debug("Enabling a user", array ("user" => $user));

        $user->setStatus(UserConstants::STATUS_ENABLED);

        if ($user->hasAnnouncement()) {
            $this->logger->debug("Enabling the announcement of the user");

            $user->getAnnouncement()->setStatus(Announcement::STATUS_ENABLED);
            $this->manager->merge($user->getAnnouncement());
        }

        if ($user->hasGroup()) {
            $this->logger->debug("Opening the group of the user");

            $user->getGroup()->setStatus(Group::STATUS_OPENED);
            $this->manager->merge($user->getGroup());
        }

        $this->manager->merge($user);
        $this->manager->flush();

        return $user;
    }

}
