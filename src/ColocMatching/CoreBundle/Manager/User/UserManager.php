<?php

namespace ColocMatching\CoreBundle\Manager\User;

use ColocMatching\CoreBundle\Entity\User\AnnouncementPreference;
use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserPreference;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Form\Type\User\AnnouncementPreferenceType;
use ColocMatching\CoreBundle\Form\Type\User\ProfileType;
use ColocMatching\CoreBundle\Form\Type\User\UserPreferenceType;
use ColocMatching\CoreBundle\Form\Type\User\UserType;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use ColocMatching\CoreBundle\Repository\User\UserRepository;
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
    private $encoder;

    /** @var LoggerInterface */
    private $logger;


    public function __construct(ObjectManager $manager, string $entityClass, EntityValidator $entityValidator,
        UserPasswordEncoderInterface $encoder, LoggerInterface $logger) {
        $this->manager = $manager;
        $this->repository = $manager->getRepository($entityClass);
        $this->entityValidator = $entityValidator;
        $this->encoder = $encoder;
        $this->logger = $logger;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::list()
     */
    public function list(PageableFilter $filter, array $fields = null): array {
        $this->logger->debug("Getting users with pagination", array ("filter" => $filter, "fields" => $fields));

        return $this->repository->findByPageable($filter, $fields);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countAll()
     */
    public function countAll(): int {
        $this->logger->debug("Counting all users");

        return $this->repository->count();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::search()
     */
    public function search(UserFilter $filter, array $fields = null): array {
        $this->logger->debug("Getting users by filtering", array ("filter" => $filter, "fields" => $fields));

        return $this->repository->findByFilter($filter, $fields);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countBy()
     */
    public function countBy(UserFilter $filter): int {
        $this->logger->debug("Counting users by filtering", array ("filter" => $filter));

        return $this->repository->countByFilter($filter);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::findByUsername()
     */
    public function findByUsername(string $username): User {
        $this->logger->debug("Getting an existing user by username", array ("username" => $username));

        /** @var User */
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
    public function create(array $data): User {
        $this->logger->debug("Creating a new user", array ("data" => $data));

        /** @var User */
        $user = $this->validateUserForm(new User(), $data, true,
            array ("validation_groups" => array ("Create", "Default")));

        $this->manager->persist($user);
        $this->manager->flush();

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
    public function update(User $user, array $data, bool $clearMissing): User {
        $this->logger->debug("Updating an existing user",
            array ("user" => $user, "data" => $data, "clearMissing" => $clearMissing));

        /** @var User */
        $updatedUser = $this->validateUserForm($user, $data, $clearMissing,
            array ("validation_groups" => array ("FullUpdate", "Default")));

        $this->manager->persist($updatedUser);
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
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::uploadProfilePicture()
     */
    public function uploadProfilePicture(User $user, File $file): ProfilePicture {
        /** @var ProfilePicture */
        $picture = empty($user->getPicture()) ? new ProfilePicture() : $user->getPicture();

        $this->logger->debug("Uploading a profile picture for an existing user",
            array ("user" => $user, "file" => $file));

        $uploadedPicture = $this->entityValidator->validateDocumentForm($picture, $file, ProfilePicture::class);
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
    public function updateProfile(User $user, array $data, bool $clearMissing): Profile {
        $this->logger->debug("Updating a user's profile",
            array ("user" => $user, "data" => $data, "clearMissing" => $clearMissing));

        $profile = $this->entityValidator->validateEntityForm($user->getProfile(), $data, ProfileType::class,
            $clearMissing);

        $this->manager->persist($profile);
        $this->manager->flush();

        return $profile;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::updateAnnouncementPreference()
     */
    public function updateAnnouncementPreference(User $user, array $data, bool $clearMissing): AnnouncementPreference {
        $this->logger->debug("Updating a user's announcement preference",
            array ("user" => $user, "data" => $data, "clearMissing" => $clearMissing));

        $preference = $this->entityValidator->validateEntityForm($user->getAnnouncementPreference(), $data,
            AnnouncementPreferenceType::class, $clearMissing);

        $this->manager->persist($preference);
        $this->manager->flush();

        return $preference;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::updateUserPreference()
     */
    public function updateUserPreference(User $user, array $data, bool $clearMissing): UserPreference {
        $this->logger->debug("Updating a user's profile preference",
            array ("user" => $user, "data" => $data, "clearMissing" => $clearMissing));

        $preference = $this->entityValidator->validateEntityForm($user->getUserPreference(), $data,
            UserPreferenceType::class, $clearMissing);

        $this->manager->persist($preference);
        $this->manager->flush();

        return $preference;
    }


    /**
     * Validates the user data and proceeds the password
     *
     * @param User $user
     * @param array $data
     * @param bool $clearMissing
     * @param array $options
     * @return User
     * @throws InvalidFormDataException
     */
    private function validateUserForm(User $user, array $data, bool $clearMissing, array $options = []): User {
        $user = $this->entityValidator->validateEntityForm($user, $data, UserType::class, $clearMissing, $options);

        if (!empty($user->getPlainPassword())) {
            $password = $this->encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
        }

        return $user;
    }

}
