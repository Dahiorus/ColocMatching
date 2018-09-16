<?php

namespace App\Core\Manager\Announcement;

use App\Core\DTO\AbstractDto;
use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\Announcement\AnnouncementPictureDto;
use App\Core\DTO\Announcement\CommentDto;
use App\Core\DTO\Announcement\HousingDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\AnnouncementPicture;
use App\Core\Entity\Announcement\Comment;
use App\Core\Entity\Announcement\Housing;
use App\Core\Entity\User\User;
use App\Core\Entity\User\UserType;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidCreatorException;
use App\Core\Exception\InvalidInviteeException;
use App\Core\Form\Type\Announcement\AnnouncementDtoForm;
use App\Core\Form\Type\Announcement\CommentDtoForm;
use App\Core\Form\Type\Announcement\HousingDtoForm;
use App\Core\Manager\AbstractDtoManager;
use App\Core\Mapper\Announcement\AnnouncementDtoMapper;
use App\Core\Mapper\Announcement\AnnouncementPictureDtoMapper;
use App\Core\Mapper\Announcement\CommentDtoMapper;
use App\Core\Mapper\Announcement\HousingDtoMapper;
use App\Core\Mapper\User\UserDtoMapper;
use App\Core\Repository\Announcement\AnnouncementRepository;
use App\Core\Repository\Filter\Pageable\Pageable;
use App\Core\Repository\User\UserRepository;
use App\Core\Validator\FormValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;

class AnnouncementDtoManager extends AbstractDtoManager implements AnnouncementDtoManagerInterface
{
    /** @var AnnouncementRepository */
    protected $repository;

    /** @var AnnouncementDtoMapper */
    protected $dtoMapper;

    /** @var FormValidator */
    private $formValidator;

    /** @var UserRepository */
    private $userRepository;

    /** @var UserDtoMapper */
    private $userDtoMapper;

    /** @var HousingDtoMapper */
    private $housingDtoMapper;

    /** @var CommentDtoMapper */
    private $commentDtoMapper;

    /** @var AnnouncementPictureDtoMapper */
    private $pictureDtoMapper;


    public function __construct(LoggerInterface $logger,
        EntityManagerInterface $em, AnnouncementDtoMapper $dtoMapper,
        FormValidator $formValidator, UserDtoMapper $userDtoMapper, HousingDtoMapper $housingDtoMapper,
        CommentDtoMapper $commentDtoMapper, AnnouncementPictureDtoMapper $pictureDtoMapper)
    {
        parent::__construct($logger, $em, $dtoMapper);

        $this->formValidator = $formValidator;
        $this->userRepository = $em->getRepository(User::class);
        $this->userDtoMapper = $userDtoMapper;
        $this->housingDtoMapper = $housingDtoMapper;
        $this->commentDtoMapper = $commentDtoMapper;
        $this->pictureDtoMapper = $pictureDtoMapper;
    }


    /**
     * @inheritdoc
     */
    public function findByCandidate(UserDto $candidate)
    {
        $this->logger->debug("Finding an announcement having a specific candidate", array ("user" => $candidate));

        /** @var User $userEntity */
        $userEntity = $this->userRepository->find($candidate->getId());
        /** @var Announcement $announcement */
        $announcement = $this->repository->findOneByCandidate($userEntity);

        $this->logger->info("Announcement found", array ("announcement" => $announcement));

        return $this->dtoMapper->toDto($announcement);
    }


    /**
     * @inheritdoc
     */
    public function create(UserDto $user, array $data, bool $flush = true) : AnnouncementDto
    {
        $this->logger->debug("Creating a new announcement", array ("creator" => $user, "data" => $data));

        /** @var User $userEntity */
        $userEntity = $this->userDtoMapper->toEntity($user);

        if ($userEntity->hasAnnouncement())
        {
            throw new InvalidCreatorException(sprintf("The user '%s' already has an Announcement",
                $userEntity->getUsername()));
        }

        /** @var AnnouncementDto $announcementDto */
        $announcementDto = $this->formValidator->validateDtoForm(
            new AnnouncementDto(), $data, AnnouncementDtoForm::class, true);
        $announcementDto->setCreatorId($user->getId());

        $announcement = $this->dtoMapper->toEntity($announcementDto);
        $userEntity->setAnnouncement($announcement);
        $userEntity->setType(UserType::PROPOSAL);

        $this->em->persist($announcement);
        $this->em->merge($userEntity);
        $this->flush($flush);

        $this->logger->info("Announcement created", array ("announcement" => $announcement));

        return $this->dtoMapper->toDto($announcement);
    }


    /**
     * @inheritdoc
     */
    public function update(AnnouncementDto $announcement, array $data, bool $clearMissing,
        bool $flush = true) : AnnouncementDto
    {
        $this->logger->debug("Updating an existing announcement",
            array ("announcement" => $announcement, "data" => $data, "clearMissing" => $clearMissing,
                "flush" => $flush));

        /** @var AnnouncementDto $announcementDto */
        $announcementDto = $this->formValidator->validateDtoForm(
            $announcement, $data, AnnouncementDtoForm::class, $clearMissing);
        /** @var Announcement $updatedAnnouncement */
        $updatedAnnouncement = $this->em->merge($this->dtoMapper->toEntity($announcementDto));
        $this->flush($flush);

        $this->logger->info("Announcement updated", array ("announcement" => $updatedAnnouncement));

        return $this->dtoMapper->toDto($updatedAnnouncement);
    }


    /**
     * @inheritdoc
     */
    public function delete(AbstractDto $dto, bool $flush = true) : void
    {
        // we have to get the entity corresponding to the DTO
        /** @var Announcement $entity */
        $entity = $this->get($dto->getId());

        $this->logger->debug("Deleting an entity",
            array ("domainClass" => $this->getDomainClass(), "id" => $dto->getId(), "flush" => $flush));

        // removing the relationship between the announcement to delete and its creator
        $creator = $entity->getCreator();
        $creator->setAnnouncement(null);

        $this->em->merge($creator);
        $this->em->remove($entity);
        $this->flush($flush);

        $this->logger->debug("Entity deleted", array ("domainClass" => $this->getDomainClass(), "id" => $dto->getId()));
    }


    /**
     * @inheritdoc
     */
    public function getHousing(AnnouncementDto $announcement) : HousingDto
    {
        $this->logger->debug("Getting an announcement housing", array ("announcement" => $announcement));

        /** @var Announcement $entity */
        $entity = $this->dtoMapper->toEntity($announcement);

        return $this->housingDtoMapper->toDto($entity->getHousing());
    }


    /**
     * @inheritdoc
     */
    public function updateHousing(AnnouncementDto $announcement, array $data, bool $clearMissing,
        bool $flush = true) : HousingDto
    {
        $this->logger->debug("Updating an announcement housing",
            array ("announcement" => $announcement, "data" => $data, "clearMissing" => $clearMissing,
                "flush" => $flush));

        /** @var HousingDto $housingDto */
        $housingDto = $this->formValidator->validateDtoForm(
            $this->getHousing($announcement), $data, HousingDtoForm::class, $clearMissing);
        /** @var Housing $entity */
        $entity = $this->em->merge($this->housingDtoMapper->toEntity($housingDto));
        $this->flush($flush);

        $this->logger->info("Announcement housing updated", array ("housing" => $entity));

        return $this->housingDtoMapper->toDto($entity);
    }


    /**
     * @inheritdoc
     */
    public function getCandidates(AnnouncementDto $announcement) : array
    {
        $this->logger->debug("Getting an announcement candidates", array ("announcement" => $announcement));

        /** @var Announcement $entity */
        $entity = $this->get($announcement->getId());

        $this->logger->info("Candidates found", array ("candidates" => $entity->getCandidates()));

        return $entity->getCandidates()->map(function (User $candidate) {
            return $this->userDtoMapper->toDto($candidate);
        })->toArray();
    }


    /**
     * @inheritdoc
     */
    public function addCandidate(AnnouncementDto $announcement, UserDto $candidate, bool $flush = true) : UserDto
    {
        $this->logger->debug("Adding a candidate to an announcement",
            array ("announcement" => $announcement, "candidate" => $candidate));

        if ($candidate->getId() == $announcement->getCreatorId()
            || $candidate->getType() == UserType::PROPOSAL)
        {
            throw new InvalidInviteeException($this->userDtoMapper->toEntity($candidate),
                sprintf("Cannot add the user '%s' to the announcement", $candidate->getEmail()));
        }

        /** @var Announcement $entity */
        $entity = $this->get($announcement->getId());
        $entity->addCandidate($this->userRepository->find($candidate->getId()));
        $entity = $this->em->merge($entity);
        $this->flush($flush);

        $this->logger->info("Candidate added", array ("announcement" => $entity));

        return $candidate;
    }


    /**
     * @inheritdoc
     */
    public function removeCandidate(AnnouncementDto $announcement, UserDto $candidate, bool $flush = true) : void
    {
        $this->logger->debug("Removing a candidate from an announcement",
            array ("announcement" => $announcement, "candidate" => $candidate));

        /** @var Announcement $entity */
        $entity = $this->get($announcement->getId());

        if ($entity->getCandidates()->filter(function (User $u) use ($candidate) {
            return $u->getId() == $candidate->getId();
        })->isEmpty())
        {
            throw new EntityNotFoundException($candidate->getEntityClass(), "id", $candidate->getId());
        }

        $this->logger->debug("Candidate to remove found in the announcement");

        /** @var User $userEntity */
        $userEntity = $this->userRepository->find($candidate->getId());
        $entity->removeCandidate($userEntity);
        $entity = $this->em->merge($entity);
        $this->flush($flush);

        $this->logger->debug("Candidate removed", array ("announcement" => $entity));
    }


    /**
     * @inheritdoc
     */
    public function hasCandidate(AnnouncementDto $announcement, UserDto $user) : bool
    {
        $this->logger->debug("Testing if an announcement has the user as a candidate",
            array ("announcement" => $announcement, "user" => $user));

        /** @var Announcement $entity */
        $entity = $this->get($announcement->getId());
        /** @var User $userEntity */
        $userEntity = $this->userRepository->find($user->getId());

        if (empty($userEntity))
        {
            throw new EntityNotFoundException($user->getEntityClass(), "id", $user->getId());
        }

        return $entity->hasInvitee($userEntity);
    }


    /**
     * @inheritdoc
     */
    public function getComments(AnnouncementDto $announcement, Pageable $pageable = null) : array
    {
        $this->logger->debug("Getting an announcement comments",
            array ("announcement" => $announcement, "page" => $pageable->getPage(), "size" => $pageable->getSize()));

        /** @var Announcement $entity */
        $entity = $this->get($announcement->getId());
        /** @var Comment[] $comments */
        $comments = empty($pageable) ? $entity->getComments()->toArray()
            : $entity->getComments()->slice($pageable->getOffset(), $pageable->getSize());

        $this->logger->info("Announcement comments found", array ("comments" => $comments));

        return $this->convertEntityListToDto($comments, $this->commentDtoMapper);
    }


    /**
     * @inheritdoc
     */
    public function countComments(AnnouncementDto $announcement) : int
    {
        $this->logger->debug("Counting an announcement comments", array ("announcement" => $announcement));

        /** @var Announcement $entity */
        $entity = $this->get($announcement->getId());

        return $entity->getComments()->count();
    }


    /**
     * @inheritdoc
     */
    public function createComment(AnnouncementDto $announcement, UserDto $author, array $data,
        bool $flush = true) : CommentDto
    {
        $this->logger->debug("Creating a new comment for an announcement",
            array ("announcement" => $announcement, "author" => $author, "data" => $data, "flush" => $flush));

        /** @var CommentDto $commentDto */
        $commentDto = $this->formValidator->validateDtoForm(new CommentDto(), $data, CommentDtoForm::class, true);
        $commentDto->setAuthorId($author->getId());
        /** @var Comment $comment */
        $comment = $this->commentDtoMapper->toEntity($commentDto);

        /** @var Announcement $entity */
        $entity = $this->get($announcement->getId());
        $entity->addComment($comment);
        $this->em->persist($comment);
        $this->em->merge($entity);
        $this->flush($flush);

        $this->logger->info("Announcement comment created", array ("comment" => $comment));

        return $this->commentDtoMapper->toDto($comment);
    }


    /**
     * @inheritdoc
     */
    public function deleteComment(AnnouncementDto $announcement, CommentDto $comment, bool $flush = true) : void
    {
        $this->logger->debug("Deleting a comment from an announcement",
            array ("announcement" => $announcement, "comment" => $comment, "flush" => $flush));

        /** @var Announcement $entity */
        $entity = $this->get($announcement->getId());

        if ($entity->getComments()->filter(function (Comment $c) use ($comment) {
            return $c->getId() == $comment->getId();
        })->isEmpty())
        {
            throw new EntityNotFoundException($comment->getEntityClass(), "id", $comment->getId());
        }

        $this->logger->debug("Comment to delete found in the announcement");

        /** @var Comment $commentEntity */
        $commentEntity = $this->em->find(Comment::class, $comment->getId());
        $entity->removeComment($commentEntity);
        $this->em->remove($commentEntity);
        $this->em->merge($entity);
        $this->flush($flush);

        $this->logger->debug("Announcement comment deleted");
    }


    /**
     * @inheritdoc
     */
    public function uploadAnnouncementPicture(AnnouncementDto $announcement, File $file,
        bool $flush = true) : AnnouncementPictureDto
    {
        $this->logger->debug("Uploading an announcement picture",
            array ("announcement" => $announcement, "file" => $file, "flush" => $flush));

        /** @var AnnouncementPictureDto $pictureDto */
        $pictureDto = $this->formValidator->validatePictureDtoForm(
            new AnnouncementPictureDto(), $file, AnnouncementPictureDto::class);
        $pictureDto->setAnnouncementId($announcement->getId());
        /** @var AnnouncementPicture $picture */
        $picture = $this->pictureDtoMapper->toEntity($pictureDto);

        $picture->getAnnouncement()->addPicture($picture);
        $this->em->persist($picture);
        $this->em->merge($picture->getAnnouncement());
        $this->flush($flush);

        $this->logger->info("Announcement picture uploaded", array ("picture" => $picture));

        return $this->pictureDtoMapper->toDto($picture);
    }


    /**
     * @inheritdoc
     */
    public function deleteAnnouncementPicture(AnnouncementDto $announcement, AnnouncementPictureDto $picture,
        bool $flush = true) : void
    {
        $this->logger->debug("Deleting an announcement picture",
            array ("announcement" => $announcement, "picture" => $picture, "flush" => $flush));

        /** @var Announcement $entity */
        $entity = $this->get($announcement->getId());

        if ($entity->getPictures()->filter(function (AnnouncementPicture $p) use ($picture) {
            return $p->getId() == $picture->getId();
        })->isEmpty())
        {
            throw new EntityNotFoundException($picture->getEntityClass(), "id", $picture->getId());
        }

        $this->logger->debug("Announcement picture to delete found in the announcement");

        /** @var AnnouncementPicture $pictureEntity */
        $pictureEntity = $this->em->find(AnnouncementPicture::class, $picture->getId());
        $entity->removePicture($pictureEntity);
        $this->em->remove($pictureEntity);
        $this->em->merge($entity);
        $this->flush($flush);

        $this->logger->debug("Announcement picture deleted", array ("pictureId" => $picture->getId()));
    }


    protected function getDomainClass() : string
    {
        return Announcement::class;
    }

}
