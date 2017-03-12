<?php

namespace ColocMatching\CoreBundle\Manager\User;

use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Form\Type\DocumentType;
use ColocMatching\CoreBundle\Form\Type\User\ProfileType;
use ColocMatching\CoreBundle\Form\Type\User\UserType;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use ColocMatching\CoreBundle\Repository\User\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var UserPasswordEncoderInterface */
    private $encoder;

    /** @var LoggerInterface */
    private $logger;


    public function __construct(ObjectManager $manager, string $entityClass, FormFactoryInterface $formFactory,
        UserPasswordEncoderInterface $encoder, LoggerInterface $logger) {
        $this->manager = $manager;
        $this->repository = $manager->getRepository($entityClass);
        $this->formFactory = $formFactory;
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
        $user = $this->processUserForm(new User(), $data, "POST",
            [ "validation_groups" => [ "Create", "Default"]]);

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
        $updatedUser = $this->processUserForm($user, $data, "PUT",
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

        $updatedUser = $this->processUserForm($user, $data, 'PATCH');

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

        $picture = $this->processFileForm($picture, $file);
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

        $updatedProfile = $this->processProfileForm($user->getProfile(), $data, "PUT");

        $this->manager->persist($updatedProfile);
        $this->manager->flush();

        return $updatedProfile;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::partialUpdateProfile()
     */
    public function partialUpdateProfile(User $user, array $data): Profile {
        $this->logger->debug(sprintf("Update (partial) a User's profile [id: %s]", $user->getId()));

        $updatedProfile = $this->processProfileForm($user->getProfile(), $data, "PATCH");

        $this->manager->persist($updatedProfile);
        $this->manager->flush();

        return $updatedProfile;
    }


    /**
     * Process the data in the user validation form.
     *
     * @param User $user
     * @param array $data
     * @param string $httpMethod
     * @param array $options
     * @return User
     * @throws InvalidFormDataException
     */
    private function processUserForm(User $user, array $data, string $httpMethod, array $options = []): User {
        /** @var \Symfony\Component\Form\FormInterface */
        $form = $this->formFactory->create(UserType::class, $user, $options);

        if (!$form->submit($data, $httpMethod != "PATCH")->isValid()) {
            $this->logger->error(sprintf("Error while trying to process the User"),
                [ "method" => $httpMethod, "user" => $user, "data" => $data, "form" => $form]);

            throw new InvalidFormDataException("Invalid submitted data in the User form", $form->getErrors(true, true));
        }

        if (!empty($user->getPlainPassword())) {
            $password = $this->encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
        }

        $this->logger->debug(sprintf("Process a User [method: '%s', user: %s]", $httpMethod, $user),
            [ 'data' => $data, 'method' => $httpMethod]);

        return $user;
    }


    /**
     * Process the file in the document validation form
     *
     * @param ProfilePicture $picture
     * @param File $file
     * @throws InvalidFormDataException
     * @return ProfilePicture
     */
    private function processFileForm(ProfilePicture $picture, File $file): ProfilePicture {
        /** @var DocumentType */
        $form = $this->formFactory->create(DocumentType::class, $picture, [ "data_class" => ProfilePicture::class]);

        if (!$form->submit([ "file" => $file, true])->isValid()) {
            $this->logger->error(sprintf("Error while trying to upload a profile picture"),
                [ "picture" => $picture, "file" => $file, "form" => $form]);

            throw new InvalidFormDataException("Invalid submitted data in the Document form",
                $form->getErrors(true, true));
        }

        $this->logger->debug(
            sprintf("Process a ProfilePicture [picture: %s]", $picture, [ "picture" => $picture, "file" => $file]));

        return $picture;
    }


    /**
     * Process the data in the profile validation form
     *
     * @param Profile $profile
     * @param array $data
     * @param string $httpMethod
     * @param array $options
     * @throws InvalidFormDataException
     * @return Profile
     */
    private function processProfileForm(Profile $profile, array $data, string $httpMethod, array $options = []): Profile {
        /** @var \Symfony\Component\Form\FormInterface */
        $form = $this->formFactory->create(ProfileType::class, $profile, $options);

        if (!$form->submit($data, $httpMethod != "PATCH")->isValid()) {
            $this->logger->error(sprintf("Error while trying to process the Profile"),
                [ "method" => $httpMethod, "profile" => $profile, "data" => $data, "form" => $form]);

            throw new InvalidFormDataException("Invalid submitted data in the profile form",
                $form->getErrors(true, true));
        }

        $this->logger->debug(sprintf("Process a Profile [method: '%s', profile: %s]", $httpMethod, $profile),
            [ "data" => $data, "method" => $httpMethod]);

        return $profile;
    }

}
