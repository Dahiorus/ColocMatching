<?php

namespace ColocMatching\CoreBundle\Manager\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;
use ColocMatching\CoreBundle\Entity\Announcement\Comment;
use ColocMatching\CoreBundle\Entity\Announcement\Housing;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\AnnouncementPictureNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidCreatorException;
use ColocMatching\CoreBundle\Exception\InvalidInviteeException;
use ColocMatching\CoreBundle\Form\Type\Announcement\AnnouncementType;
use ColocMatching\CoreBundle\Form\Type\Announcement\CommentType;
use ColocMatching\CoreBundle\Form\Type\Announcement\HousingType;
use ColocMatching\CoreBundle\Repository\Announcement\AnnouncementRepository;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Validator\EntityValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;

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
    public function list(PageableFilter $filter, array $fields = null) : array {
        $this->logger->debug("Getting announcements with pagination", array ("filter" => $filter, "fields" => $fields));

        return $this->repository->findByPageable($filter, $fields);
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countAll()
     */
    public function countAll() : int {
        $this->logger->debug("Counting all Announcements");

        return $this->repository->countAll();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::search()
     */
    public function search(AnnouncementFilter $filter, array $fields = null) : array {
        $this->logger->debug("Searching announcements", array ("filter" => $filter, "fields" => $fields));

        return $this->repository->findByFilter($filter, $fields);
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countBy()
     */
    public function countBy(AnnouncementFilter $filter) : int {
        $this->logger->debug("Counting announcements by filtering", array ("filter" => $filter));

        return $this->repository->countByFilter($filter);
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::create()
     */
    public function create(User $user, array $data) : Announcement {
        $this->logger->debug("Creating a new announcement", array ("creator" => $user, "data" => $data));

        if ($user->hasAnnouncement()) {
            throw new InvalidCreatorException(sprintf("The user '%s' already has an Announcement",
                $user->getUsername()));
        }

        /** @var Announcement $announcement */
        $announcement = $this->entityValidator->validateEntityForm(new Announcement($user), $data,
            AnnouncementType::class, true);
        $user->setAnnouncement($announcement);

        $this->manager->persist($announcement);
        $this->manager->flush();

        return $announcement;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::read()
     */
    public function read(int $id, array $fields = null) {
        $this->logger->debug("Getting an existing announcement", array ("id" => $id, "fields" => $fields));

        /** @var Announcement */
        $announcement = $this->repository->findById($id, $fields);

        if (empty($announcement)) {
            throw new AnnouncementNotFoundException("id", $id);
        }

        return $announcement;
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::update()
     */
    public function update(Announcement $announcement, array $data, bool $clearMissing) : Announcement {
        $this->logger->debug("Updating an existing announcement",
            array ("announcement" => $announcement, "data" => $data, "clearMissing" => $clearMissing));

        /** @var Announcement $updatedAnnouncement */
        $updatedAnnouncement = $this->entityValidator->validateEntityForm($announcement, $data, AnnouncementType::class,
            $clearMissing, array ("location_data" => $announcement->getLocation()));

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
    public function uploadAnnouncementPicture(Announcement $announcement, File $file) : Collection {
        $this->logger->debug("Uploading a new picture for an announcement",
            array ("announcement" => $announcement, "file" => $file));

        /** @var AnnouncementPicture $picture */
        $picture = $this->entityValidator->validatePictureForm(new AnnouncementPicture($announcement), $file,
            AnnouncementPicture::class);

        $announcement->addPicture($picture);

        $this->manager->persist($announcement);
        $this->manager->flush();

        $this->logger->debug("New picture uploaded", array ("picture" => $picture));

        return $announcement->getPictures();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::readAnnouncementPicture()
     */
    public function readAnnouncementPicture(Announcement $announcement, int $pictureId) : AnnouncementPicture {
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
        $this->manager->flush();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::addCandidate()
     */
    public function addCandidate(Announcement $announcement, User $user) : Collection {
        $this->logger->debug("Adding an candidate to an existing announcement",
            array ("announcement" => $announcement, "user" => $user));

        if ($announcement->getCreator() === $user || $user->getType() == UserConstants::TYPE_PROPOSAL) {
            throw new InvalidInviteeException($user,
                sprintf("Cannot add the user '%s' to the announcement", $user->getUsername()));
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

        /** @var Collection $candidates */
        $candidates = $announcement->getCandidates();

        foreach ($candidates as $candidate) {
            if ($candidate->getId() == $userId) {
                $this->logger->debug("Candidate found", array ("user" => $candidate, "announcement" => $announcement));

                $announcement->removeCandidate($candidate);
                $this->manager->persist($announcement);

                break;
            }
        }

        $this->manager->flush();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::updateHousing()
     */
    public function updateHousing(Announcement $announcement, array $data, bool $clearMissing) : Housing {
        $this->logger->debug("Updating the housing of an existing announcement",
            array ("announcement" => $announcement, "data" => $data, "clearMissing" => $clearMissing));

        /** @var Housing $updatedHousing */
        $updatedHousing = $this->entityValidator->validateEntityForm($announcement->getHousing(), $data,
            HousingType::class, $clearMissing);

        $this->manager->persist($updatedHousing);
        $this->manager->flush();

        return $updatedHousing;
    }


    /**
     * @inheritdoc
     */
    public function findByCandidate(User $candidate) {
        $this->logger->debug("Finding an announcement having a specific candidate", array ("user" => $candidate));

        return $this->repository->findOneByCandidate($candidate);
    }


    /**
     * @inheritdoc
     */
    public function getComments(Announcement $announcement, PageableFilter $filter) : array {
        $this->logger->debug("Getting the comments of an announcement",
            array ("announcement" => $announcement, "filter" => $filter));

        $comments = $announcement->getComments()->toArray();
        $offset = $filter->getOffset();
        $length = $filter->getSize();

        return array_slice($comments, $offset, $length);
    }


    /**
     * @inheritdoc
     */
    public function createComment(Announcement $announcement, User $author, array $data) : Comment {
        $this->logger->debug("Creating a new comment for an announcement",
            array ("announcement" => $announcement, "author" => $author, "data" => $data));

        /** @var Comment $comment */
        $comment = $this->entityValidator->validateEntityForm(new Comment($author), $data, CommentType::class, true);
        $announcement->addComment($comment);

        $this->manager->persist($comment);
        $this->manager->persist($announcement);
        $this->manager->flush();

        return $comment;
    }


    /**
     * @inheritdoc
     */
    public function deleteComment(Announcement $announcement, int $id) {
        $this->logger->debug("Deleting a comment from an announcement",
            array ("announcement" => $announcement, "id" => $id));

        foreach ($announcement->getComments() as $comment) {
            if ($comment->getId() == $id) {
                $this->logger->debug("Comment found", array ("comment" => $comment, "announcement" => $announcement));

                $announcement->removeComment($comment);
                $this->manager->persist($announcement);

                break;
            }
        }

        $this->manager->flush();
    }

}
