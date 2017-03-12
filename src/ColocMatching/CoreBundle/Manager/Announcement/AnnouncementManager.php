<?php

namespace ColocMatching\CoreBundle\Manager\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\AnnouncementPictureNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Announcement\AnnouncementType;
use ColocMatching\CoreBundle\Form\Type\DocumentType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface;
use ColocMatching\CoreBundle\Repository\Announcement\AnnouncementRepository;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * CRUD Manager of entity Announcement
 *
 * @author brondon.ung
 */
class AnnouncementManager implements AnnouncementManagerInterface {

    /** @var ObjectManager */
    private $manager;

    /** @var AnnouncementRepository */
    private $repository;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var LoggerInterface */
    private $logger;


    public function __construct(ObjectManager $manager, string $entityClass, FormFactoryInterface $formFactory,
        LoggerInterface $logger) {
        $this->manager = $manager;
        $this->repository = $manager->getRepository($entityClass);
        $this->formFactory = $formFactory;
        $this->logger = $logger;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::list()
     */
    public function list(AbstractFilter $filter, array $fields = null): array {
        if (!empty($fields)) {
            $this->logger->debug(
                sprintf("Get all Announcements [filter: %s | fields: [%s]]", $filter, implode(", ", $fields)));

            return $this->repository->selectFieldsByPage($fields, $filter);
        }

        $this->logger->debug(sprintf("Get all Announcement [filter: %s]", $filter));

        return $this->repository->findByPage($filter);
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countAll()
     */
    public function countAll(): int {
        $this->logger->debug('Count all Announcements');

        return $this->repository->count();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::search()
     */
    public function search(AnnouncementFilter $filter, array $fields = null): array {
        if (!empty($fields)) {
            $this->logger->debug(
                sprintf("Get Announcements by AnnouncementFilter [filter: %s | fields: [%s]]", $filter,
                    implode(', ', $fields)));

            return $this->repository->selectFieldsByFilter($filter, $fields);
        }

        $this->logger->debug(sprintf("Get Announcements by AnnouncementFilter [filter: %s]", $filter));

        return $this->repository->findByFilter($filter);
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countBy()
     */
    public function countBy(AbstractFilter $filter): int {
        $this->logger->debug(sprintf("Count all announcements by filter [filter: %s]", $filter));

        return $this->repository->countByFilter($filter);
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::create()
     */
    public function create(User $user, array $data): Announcement {
        $this->logger->debug(sprintf("Create a new Announcement for the User [id: %d]", $user->getId()));

        if (!empty($user->getAnnouncement())) {
            throw new UnprocessableEntityHttpException(
                sprintf("The user '%s' already has an Announcement", $user->getUsername()));
        }

        /** @var Announcement */
        $announcement = $this->processForm(new Announcement($user), $data, "POST");

        $user->setAnnouncement($announcement);

        $this->manager->persist($announcement);
        $this->manager->persist($user);
        $this->manager->flush();

        return $announcement;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::read()
     */
    public function read(int $id, array $fields = null) {
        /** @var Announcement */
        $announcement = null;

        if (!empty($fields)) {
            $this->logger->debug(
                sprintf("Get an Announcement by id [id: %d | fields: [%s]]", $id, implode(", ", $fields)));

            $announcement = $this->repository->selectFieldsFromOne($id, $fields);
        }
        else {
            $this->logger->debug(sprintf("Get a User by id [id: %d]", $id));

            $announcement = $this->repository->find($id);
        }

        if (empty($announcement)) {
            throw new AnnouncementNotFoundException("id", $id);
        }

        return $announcement;
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::update()
     */
    public function update(Announcement $announcement, array $data): Announcement {
        $this->logger->debug(sprintf("Update the following Announcement [id : %d]", $announcement->getId()));

        $updatedAnnouncement = $this->processForm($announcement, $data, "PUT");

        $this->manager->persist($updatedAnnouncement);
        $this->manager->flush();

        return $updatedAnnouncement;
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::delete()
     */
    public function delete(Announcement $announcement) {
        $this->logger->debug(sprintf("Delete an existing Announcement [id: %d]", $announcement->getId()));

        $this->manager->remove($announcement);
        $this->manager->flush();
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::partialUpdate()
     */
    public function partialUpdate(Announcement $announcement, array $data): Announcement {
        $this->logger->debug(sprintf("Update (partial) the following Announcement [id: %d]", $announcement->getId()));

        /** @var Announcement */
        $updatedAnnouncement = $this->processForm($announcement, $data, "PATCH");

        $this->manager->persist($updatedAnnouncement);
        $this->manager->flush();

        return $updatedAnnouncement;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::uploadAnnouncementPicture()
     */
    public function uploadAnnouncementPicture(Announcement $announcement, File $file): Announcement {
        $this->logger->debug(sprintf("Upload a new picture for an Announcement [id: %d]", $announcement->getId()),
            [ "announcement" => $announcement, "file" => $file]);

        /** @var AnnouncementPicture */
        $picture = $this->processFileForm(new AnnouncementPicture($announcement), $file);

        $announcement->addPicture($picture);
        $announcement->setLastUpdate(new \DateTime());

        $this->manager->persist($picture);
        $this->manager->persist($announcement);
        $this->manager->flush();

        $this->logger->debug(
            sprintf("New picture uploaded for the announcement [id: %d, announcementPicture: %s]",
                $announcement->getId(), $picture), [ "announcement" => $announcement, "picture" => $picture]);

        return $announcement;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::readAnnouncementPicture()
     */
    public function readAnnouncementPicture(Announcement $announcement, int $pictureId): AnnouncementPicture {
        $this->logger->debug(
            sprintf("Get a picture of an existing announcement [announcement: %s, pictureId: %d]", $announcement,
                $pictureId), [ "announcement" => $announcement, "pictureId" => $pictureId]);

        /** @var ArrayCollection */
        $pictures = $announcement->getPictures();

        foreach ($pictures as $picture) {
            if ($picture->getId() == $pictureId) {
                return $picture;
            }
        }

        throw new AnnouncementPictureNotFoundException("id", $pictureId);
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::deleteAnnouncementPicture()
     */
    public function deleteAnnouncementPicture(AnnouncementPicture $picture) {
        /** @var Announcement */
        $announcement = $picture->getAnnouncement();

        $this->logger->debug(
            sprintf("Delete a picture of an existing announcement [announcementId: %d, pictureId: %d]",
                $announcement->getId(), $picture->getId()), [ "announcement" => $announcement, "picture" => $picture]);

        $announcement->removePicture($picture);

        $this->manager->remove($picture);
        $this->manager->persist($announcement);
        $this->manager->flush();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::addNewCandidate()
     */
    public function addNewCandidate(Announcement $announcement, User $user): Announcement {
        $this->logger->debug(
            sprintf("Add an candidate to an existing announcement [id: %d, userId: %d]", $announcement->getId(),
                $user->getId()), [ "announcement" => $announcement, "user" => $user]);

        if ($announcement->getCreator() == $user) {
            throw new UnprocessableEntityHttpException(
                "The announcement creator cannot be a candidate of his own announcement");
        }

        $announcement->addCandidate($user);

        $this->manager->persist($announcement);
        $this->manager->flush();

        return $announcement;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::removeCandidate()
     */
    public function removeCandidate(Announcement $announcement, int $userId): Announcement {
        $this->logger->debug(
            sprintf("Remove a candidate from an existing announcement [id: %d, userId: %d]", $announcement->getId(),
                $userId), [ "announcement" => $announcement, "userId" => $userId]);

        /** @var ArrayCollection */
        $candidates = $announcement->getCandidates();
        /** @var User */
        $userTarget = null;

        foreach ($candidates as $candidate) {
            if ($candidate->getId() == $userId) {
                $userTarget = $candidate;
                break;
            }
        }

        if (!empty($userTarget)) {
            $this->logger->debug(sprintf("Candidate found [userId: %d]", $userTarget->getId()),
                [ "user" => $userTarget, "announcement" => $announcement]);

            $announcement->removeCandidate($userTarget);

            $this->manager->persist($announcement);
            $this->manager->flush();
        }

        return $announcement;
    }


    /**
     * Process the data in the announcement validation form
     *
     * @param Announcement $announcement
     * @param array $data
     * @param string $httpMethod
     * @param array $options
     * @return Announcement
     */
    private function processForm(Announcement $announcement, array $data, string $httpMethod, array $options = []): Announcement {
        /** @var array */
        $fullOptions = array_merge([ 'method' => $httpMethod], $options);
        /** @var \Symfony\Component\Form\FormInterface */
        $form = $this->formFactory->create(AnnouncementType::class, $announcement, $fullOptions);

        if (!$form->submit($data, $httpMethod != "PATCH")->isValid()) {
            $this->logger->error(sprintf("Error while trying to process the Announcement"),
                [ "method" => $httpMethod, "announcement" => $announcement, "data" => $data, "form" => $form]);

            throw new InvalidFormDataException("Invalid submitted data in the Announcement form",
                $form->getErrors(true, true));
        }

        $announcement->setLastUpdate(new \DateTime());

        $this->logger->debug(
            sprintf("Process an Announcement [method: '%s' | announcement: %s]", $httpMethod, $announcement),
            [ "data" => $data, "method" => $httpMethod]);

        return $announcement;
    }


    /**
     * Process the file in the document validation form
     *
     * @param AnnouncementPicture $picture
     * @param File $file
     * @throws InvalidFormDataException
     * @return AnnouncementPicture
     */
    private function processFileForm(AnnouncementPicture $picture, File $file): AnnouncementPicture {
        /** @var DocumentType */
        $form = $this->formFactory->create(DocumentType::class, $picture, [
            "data_class" => AnnouncementPicture::class]);

        if (!$form->submit([ "file" => $file, true])->isValid()) {
            $this->logger->error(sprintf("Error while trying to upload an announcement picture"),
                [ "picture" => $picture, "file" => $file, "form" => $form]);

            throw new InvalidFormDataException("Invalid submitted data in the Document form",
                $form->getErrors(true, true));
        }

        $this->logger->debug(
            sprintf("Process an AnnouncementPicture [picture: %s]", $picture, [
                "picture" => $picture,
                "file" => $file]));

        return $picture;
    }

}