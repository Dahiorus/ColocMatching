<?php

namespace ColocMatching\CoreBundle\Manager\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;
use ColocMatching\CoreBundle\Entity\Announcement\Housing;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\AnnouncementPictureNotFoundException;
use ColocMatching\CoreBundle\Form\Type\Announcement\AnnouncementType;
use ColocMatching\CoreBundle\Form\Type\Announcement\HousingType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface;
use ColocMatching\CoreBundle\Validator\EntityValidator;
use ColocMatching\CoreBundle\Repository\Announcement\AnnouncementRepository;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use ColocMatching\CoreBundle\Entity\User\UserConstants;

/**
 * CRUD Manager of the entity Announcement
 *
 * @author brondon.ung
 */
class AnnouncementManager implements AnnouncementManagerInterface {

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var EntityValidator
     */
    private $entityValidator;

    /**
     * @var AnnouncementRepository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(ObjectManager $manager, string $entityClass, EntityValidator $entityValidator,
        LoggerInterface $logger) {
        $this->manager = $manager;
        $this->repository = $manager->getRepository($entityClass);
        $this->entityValidator = $entityValidator;
        $this->logger = $logger;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::list()
     */
    public function list(PageableFilter $filter, array $fields = null): array {
        if (!empty($fields)) {
            $this->logger->debug("Getting announcements with pagination",
                array ("filter" => $filter, "fields" => $fields));

            return $this->repository->findByPageable($filter, $fields);
        }

        $this->logger->debug("Getting announcements with pagination", array ("filter" => $filter));

        return $this->repository->findByPageable($filter);
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countAll()
     */
    public function countAll(): int {
        $this->logger->debug("Counting all Announcements");

        return $this->repository->count();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::search()
     */
    public function search(AnnouncementFilter $filter, array $fields = null): array {
        if (!empty($fields)) {
            $this->logger->debug("Searching announcements", array ("filter" => $filter, "fields" => $fields));

            return $this->repository->selectFieldsByFilter($filter, $fields);
        }

        $this->logger->debug("Searching announcements", array ("filter" => $filter));

        return $this->repository->findByFilter($filter);
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countBy()
     */
    public function countBy(AnnouncementFilter $filter): int {
        $this->logger->debug("Counting announcements by filtering", array ("filter" => $filter));

        return $this->repository->countByFilter($filter);
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::create()
     */
    public function create(User $user, array $data): Announcement {
        $this->logger->debug("Creating a new announcement", array ("creator" => $user, "data" => $data));

        if (!empty($user->getAnnouncement())) {
            throw new UnprocessableEntityHttpException(
                sprintf("The user '%s' already has an Announcement", $user->getUsername()));
        }

        /** @var Announcement */
        $announcement = $this->entityValidator->validateEntityForm(new Announcement($user), $data,
            AnnouncementType::class, true);
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
            $this->logger->debug("Getting an existing announcement", array ("id" => $id, "fields" => $fields));

            $announcement = $this->repository->selectFieldsFromOne($id, $fields);
        }
        else {
            $this->logger->debug("Getting an existing announcement", array ("id" => $id));

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
    public function update(Announcement $announcement, array $data, bool $clearMissing): Announcement {
        $this->logger->debug("Updating an existing announcement",
            array ("announcement" => $announcement, "data" => $data, "clearMissing" => $clearMissing));

        /** @var Announcement */
        $updatedAnnouncement = $this->entityValidator->validateEntityForm($announcement, $data, AnnouncementType::class,
            $clearMissing);

        $this->manager->persist($updatedAnnouncement);
        $this->manager->flush();

        return $updatedAnnouncement;
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::delete()
     */
    public function delete(Announcement $announcement) {
        $this->logger->debug("Deleting an existing announcement", array ("announcement" => $announcement));

        $this->manager->remove($announcement);
        $this->manager->flush();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::uploadAnnouncementPicture()
     */
    public function uploadAnnouncementPicture(Announcement $announcement, File $file): Collection {
        $this->logger->debug("Uploading a new picture for an announcement",
            array ("announcement" => $announcement, "file" => $file));

        /** @var AnnouncementPicture */
        $picture = $this->entityValidator->validateDocumentForm(new AnnouncementPicture($announcement), $file,
            AnnouncementPicture::class);

        $announcement->addPicture($picture);

        $this->manager->persist($picture);
        $this->manager->persist($announcement);
        $this->manager->flush();

        $this->logger->debug("New picture uploaded for an existing announcement",
            array ("announcement" => $announcement, "picture" => $picture));

        return $announcement->getPictures();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::readAnnouncementPicture()
     */
    public function readAnnouncementPicture(Announcement $announcement, int $pictureId): AnnouncementPicture {
        $this->logger->debug("Getting a picture of an existing announcement",
            array ("announcement" => $announcement, "pictureId" => $pictureId));

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

        $this->logger->debug("Deleting a picture of an existing announcement",
            array ("announcement" => $announcement, "picture" => $picture));

        $announcement->removePicture($picture);

        $this->manager->remove($picture);
        $this->manager->persist($announcement);
        $this->manager->flush();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::addCandidate()
     */
    public function addCandidate(Announcement $announcement, User $user): Collection {
        $this->logger->debug("Adding an candidate to an existing announcement",
            array ("announcement" => $announcement, "user" => $user));

        if ($announcement->getCreator() == $user) {
            throw new UnprocessableEntityHttpException(
                "The announcement creator cannot be a candidate of his own announcement");
        }

        if ($user->getType() == UserConstants::TYPE_PROPOSAL) {
            throw new UnprocessableEntityHttpException(
                sprintf("Cannot add a user with the type '%s'", UserConstants::TYPE_PROPOSAL));
        }

        $announcement->addCandidate($user);

        $this->manager->persist($announcement);
        $this->manager->flush();

        return $announcement->getCandidates();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::removeCandidate()
     */
    public function removeCandidate(Announcement $announcement, int $userId) {
        $this->logger->debug("Remove a candidate from an existing announcement",
            array ("announcement" => $announcement, "userId" => $userId));

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
            $this->logger->debug("Candidate found", array ("user" => $userTarget, "announcement" => $announcement));

            $announcement->removeCandidate($userTarget);

            $this->manager->persist($announcement);
            $this->manager->flush();
        }
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::updateHousing()
     */
    public function updateHousing(Announcement $announcement, array $data, bool $clearMissing): Housing {
        $this->logger->debug("Updating the housing of an existing announcement",
            array ("announcement" => $announcement, "data" => $data, "clearMissing" => $clearMissing));

        /** @var Housing */
        $updatedHousing = $this->entityValidator->validateEntityForm($announcement->getHousing(), $data,
            HousingType::class, $clearMissing);

        $this->manager->persist($updatedHousing);
        $this->manager->flush();

        return $updatedHousing;
    }

}