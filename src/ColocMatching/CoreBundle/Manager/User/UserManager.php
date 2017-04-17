<?php

namespace ColocMatching\CoreBundle\Manager\User;

use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Form\Type\User\ProfileType;
use ColocMatching\CoreBundle\Form\Type\User\UserType;
use ColocMatching\CoreBundle\Manager\EntityValidator;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use ColocMatching\CoreBundle\Repository\User\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use ColocMatching\CoreBundle\Entity\User\AnnouncementPreference;
use ColocMatching\CoreBundle\Entity\User\UserPreference;
use ColocMatching\CoreBundle\Form\Type\User\AnnouncementPreferenceType;
use ColocMatching\CoreBundle\Form\Type\User\UserPreferenceType;

/**
 * CRUD Manager of entity User
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
    public function list(AbstractFilter $filter, array $fields = null): array {
        if (!empty($fields)) {
            $this->logger->debug(
                sprintf("Getting all Users [filter: %s | fields: [%s]]", $filter, implode(", ", $fields)));

            return $this->repository->selectFieldsByPage($fields, $filter);
        }

        $this->logger->debug(sprintf("Getting all Users [filter: %s]", $filter));

        return $this->repository->findByPage($filter);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countAll()
     */
    public function countAll(): int {
        $this->logger->debug('Counting all Users');

        return $this->repository->count();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::search()
     */
    public function search(UserFilter $filter, array $fields = null): array {
        if (!empty($fields)) {
            $this->logger->debug(
                sprintf("Getting Users by UserFilter [filter: %s | fields: [%s]]", $filter, implode(', ', $fields)));

            return $this->repository->selectFieldsByFilter($filter, $fields);
        }

        $this->logger->debug(sprintf("Getting Users by UserFilter [filter: %s]", $filter));

        return $this->repository->findByFilter($filter);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countBy()
     */
    public function countBy(AbstractFilter $filter): int {
        $this->logger->debug(sprintf("Counting Users by filter [filter: %s]", $filter));

        return $this->repository->countByFilter($filter);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::findByUsername()
     */
    public function findByUsername(string $username): User {
        $this->logger->debug(sprintf("Getting a User by username [username: '%s']", $username));

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
        $this->logger->debug(sprintf("Creating a new User"));

        /** @var User */
        $user = $this->validateUserForm(new User(), $data, "POST", [ "validation_groups" => [
            "Create",
            "Default"]]);

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::read()
     */
    public function read(int $id, array $fields = null) {
        /** @var User */
        $user = null;

        if (!empty($fields)) {
            $this->logger->debug(sprintf("Get a User by id [id: %d | fields: [%s]]", $id, implode(", ", $fields)));
            $user = $this->repository->selectFieldsFromOne($id, $fields);
        }
        else {
            $this->logger->debug(sprintf("Get a User by id [id: %d]", $id));
            $user = $this->repository->find($id);
        }

        if (empty($user)) {
            throw new UserNotFoundException("id", $id);
        }

        return $user;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::update()
     */
    public function update(User $user, array $data): User {
        $this->logger->debug(sprintf("Update the following User [id: %d]", $user->getId()));

        /** @var User */
        $updatedUser = $this->validateUserForm($user, $data, "PUT",
            [ "validation_groups" => [ "FullUpdate", "Default"]]);

        $this->manager->persist($updatedUser);
        $this->manager->flush();

        return $updatedUser;
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::delete()
     */
    public function delete(User $user) {
        $this->logger->debug(sprintf("Delete the following User [id: %d]", $user->getId()));

        $this->manager->remove($user);
        $this->manager->flush();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::partialUpdate()
     */
    public function partialUpdate(User $user, array $data): User {
        $this->logger->debug(sprintf("Update (partial) the following User [id: %d]", $user->getId()));

        $updatedUser = $this->validateUserForm($user, $data, "PATCH");

        $this->manager->persist($updatedUser);
        $this->manager->flush();

        return $updatedUser;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::uploadProfilePicture()
     */
    public function uploadProfilePicture(User $user, File $file): ProfilePicture {
        /** @var ProfilePicture */
        $picture = (empty($user->getPicture())) ? new ProfilePicture() : $user->getPicture();

        $this->logger->debug(
            sprintf("Upload a new profile picture for the user [id: %d, picture: %s]", $user->getId(), $picture),
            [ "user" => $user, "file" => $file]);

        $picture = $this->entityValidator->validateDocumentForm($picture, $file, ProfilePicture::class);
        $user->setPicture($picture);

        $this->manager->persist($picture);
        $this->manager->persist($user);
        $this->manager->flush();

        return $user->getPicture();
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::deleteProfilePicture()
     */
    public function deleteProfilePicture(User $user) {
        $this->logger->debug(sprintf("Delete a User's profile picture [id: %d]", $user->getId()),
            [ "user" => $user]);

        /** @var ProfilePicture */
        $picture = $user->getPicture();

        if (!empty($picture)) {
            $this->logger->debug(sprintf("Profile picture found for the User [user: %s, picture: %s]", $user, $picture),
                [ "user" => $user, "picture" => $picture]);

            $this->manager->remove($picture);
            $this->manager->flush();

            $user->setPicture(null);
        }
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::updateProfile()
     */
    public function updateProfile(User $user, array $data): Profile {
        $this->logger->debug(sprintf("Update a User's profile [id: %s]", $user->getId()));

        $profile = $this->entityValidator->validateEntityForm($user->getProfile(), $data, ProfileType::class, "PUT");

        $this->manager->persist($profile);
        $this->manager->flush();

        return $profile;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::partialUpdateProfile()
     */
    public function partialUpdateProfile(User $user, array $data): Profile {
        $this->logger->debug(sprintf("Update (partial) a User's profile [id: %s]", $user->getId()));

        $profile = $this->entityValidator->validateEntityForm($user->getProfile(), $data, ProfileType::class, "PATCH");

        $this->manager->persist($profile);
        $this->manager->flush();

        return $profile;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::updateAnnouncementPreference()
     */
    public function updateAnnouncementPreference(User $user, array $data): AnnouncementPreference {
        $this->logger->debug(sprintf("Update a User's announcement preference [id: %s]", $user->getId()));

        $preference = $this->entityValidator->validateEntityForm($user->getAnnouncementPreference(), $data,
            AnnouncementPreferenceType::class, "PUT");

        $this->manager->persist($preference);
        $this->manager->flush();

        return $preference;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::partialUpdateAnnouncementPreference()
     */
    public function partialUpdateAnnouncementPreference(User $user, array $data): AnnouncementPreference {
        $this->logger->debug(sprintf("Update (partial) a User's announcement preference [id: %s]", $user->getId()));

        $preference = $this->entityValidator->validateEntityForm($user->getAnnouncementPreference(), $data,
            AnnouncementPreferenceType::class, "PATCH");

        $this->manager->persist($preference);
        $this->manager->flush();

        return $preference;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::updateUserPreference()
     */
    public function updateUserPreference(User $user, array $data): UserPreference {
        $this->logger->debug(sprintf("Update a User's user preference [id: %s]", $user->getId()));

        $preference = $this->entityValidator->validateEntityForm($user->getUserPreference(), $data,
            UserPreferenceType::class, "PUT");

        $this->manager->persist($preference);
        $this->manager->flush();

        return $preference;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::partialUpdateUserPreference()
     */
    public function partialUpdateUserPreference(User $user, array $data): UserPreference {
        $this->logger->debug(sprintf("Update (partial) a User's user preference [id: %s]", $user->getId()));

        $preference = $this->entityValidator->validateEntityForm($user->getUserPreference(), $data,
            UserPreferenceType::class, "PATCH");

        $this->manager->persist($preference);
        $this->manager->flush();

        return $preference;
    }


    /**
     * Validates the user data and proceeds the password
     *
     * @param User $user
     * @param array $data
     * @param string $httpMethod
     * @param array $options
     * @return User
     * @throws InvalidFormDataException
     */
    private function validateUserForm(User $user, array $data, string $httpMethod, array $options = []): User {
        /** @var User */
        $user = $this->entityValidator->validateEntityForm($user, $data, UserType::class, $httpMethod, $options);

        if (!empty($user->getPlainPassword())) {
            $password = $this->encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
        }

        return $user;
    }

}
