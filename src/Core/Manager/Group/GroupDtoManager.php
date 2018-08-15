<?php

namespace App\Core\Manager\Group;

use App\Core\DTO\AbstractDto;
use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\Group\GroupPictureDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Group\Group;
use App\Core\Entity\Group\GroupPicture;
use App\Core\Entity\User\User;
use App\Core\Entity\User\UserConstants;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidCreatorException;
use App\Core\Exception\InvalidInviteeException;
use App\Core\Form\Type\Group\GroupDtoForm;
use App\Core\Manager\AbstractDtoManager;
use App\Core\Mapper\Group\GroupDtoMapper;
use App\Core\Mapper\Group\GroupPictureDtoMapper;
use App\Core\Mapper\User\UserDtoMapper;
use App\Core\Repository\Group\GroupRepository;
use App\Core\Repository\User\UserRepository;
use App\Core\Validator\FormValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;

class GroupDtoManager extends AbstractDtoManager implements GroupDtoManagerInterface
{
    /** @var GroupRepository */
    protected $repository;

    /** @var GroupDtoMapper */
    protected $dtoMapper;

    /** @var FormValidator */
    private $formValidator;

    /** @var UserRepository */
    private $userRepository;

    /** @var UserDtoMapper */
    private $userDtoMapper;

    /** @var GroupPictureDtoMapper */
    private $pictureDtoMapper;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, GroupDtoMapper $dtoMapper,
        FormValidator $formValidator, UserDtoMapper $userDtoMapper, GroupPictureDtoMapper $pictureDtoMapper)
    {
        parent::__construct($logger, $em, $dtoMapper);

        $this->formValidator = $formValidator;
        $this->userRepository = $em->getRepository(User::class);
        $this->userDtoMapper = $userDtoMapper;
        $this->pictureDtoMapper = $pictureDtoMapper;
    }


    /**
     * @inheritdoc
     */
    public function findByMember(UserDto $member)
    {
        $this->logger->debug("Finding a group having a specific member", array ("user" => $member));

        /** @var User $userEntity */
        $userEntity = $this->userRepository->find($member->getId());
        /** @var Group $group */
        $group = $this->repository->findOneByMember($userEntity);

        $this->logger->info("Group found", array ("group" => $group));

        return $this->dtoMapper->toDto($group);
    }


    /**
     * @inheritdoc
     */
    public function create(UserDto $user, array $data, bool $flush = true) : GroupDto
    {
        $this->logger->debug("Creating a new group", array ("creator" => $user, "data" => $data, "flush" => $flush));

        /** @var User $userEntity */
        $userEntity = $this->userDtoMapper->toEntity($user);

        if ($userEntity->hasGroup())
        {
            throw new InvalidCreatorException(
                sprintf("The user '%s' already has a group", $userEntity->getUsername()));
        }

        /** @var GroupDto $groupDto */
        $groupDto = $this->formValidator->validateDtoForm(new GroupDto(), $data, GroupDtoForm::class, true);
        $groupDto->setCreatorId($user->getId());

        /** @var Group $group */
        $group = $this->dtoMapper->toEntity($groupDto);
        $userEntity->setGroup($group);

        $this->em->persist($group);
        $this->em->merge($userEntity);
        $this->flush($flush);

        $this->logger->info("Group created", array ("group" => $group));

        return $this->dtoMapper->toDto($group);
    }


    /**
     * @inheritdoc
     */
    public function update(GroupDto $group, array $data, bool $clearMissing, bool $flush = true) : GroupDto
    {
        $this->logger->debug("Updating a group",
            array ("group" => $group, "data" => $data, "clearMissing" => $clearMissing, "flush" => $flush));

        /** @var GroupDto $groupDto */
        $groupDto = $this->formValidator->validateDtoForm($group, $data, GroupDtoForm::class, $clearMissing);
        /** @var Group $updatedGroup */
        $updatedGroup = $this->dtoMapper->toEntity($groupDto);

        $updatedGroup = $this->em->merge($updatedGroup);
        $this->flush($flush);

        $this->logger->info("Group updated", array ("group" => $updatedGroup));

        return $this->dtoMapper->toDto($updatedGroup);
    }


    /**
     * @inheritdoc
     */
    public function delete(AbstractDto $dto, bool $flush = true) : void
    {
        // we have to get the entity corresponding to the DTO
        /** @var Group $entity */
        $entity = $this->get($dto->getId());

        $this->logger->debug("Deleting an entity",
            array ("domainClass" => $this->getDomainClass(), "id" => $dto->getId(), "flush" => $flush));

        // removing the relationship between the group to delete and its creator
        $creator = $entity->getCreator();
        $creator->setGroup(null);

        $this->em->merge($creator);
        $this->em->remove($entity);
        $this->flush($flush);

        $this->logger->debug("Entity deleted", array ("domainClass" => $this->getDomainClass(), "id" => $dto->getId()));
    }


    /**
     * @inheritdoc
     */
    public function getMembers(GroupDto $group) : array
    {
        $this->logger->debug("Getting a group members", array ("group" => $group));

        /** @var Group $entity */
        $entity = $this->get($group->getId());

        $this->logger->info("Members found", array ("members" => $entity->getMembers()));

        return $entity->getMembers()->map(function (User $member) {
            return $this->userDtoMapper->toDto($member);
        })->toArray();
    }


    /**
     * @inheritdoc
     */
    public function addMember(GroupDto $group, UserDto $member, bool $flush = true) : UserDto
    {
        $this->logger->debug("Adding a new member to a group", array ("group" => $group, "user" => $member));

        if ($member->getType() != UserConstants::TYPE_SEARCH)
        {
            throw new InvalidInviteeException($this->userDtoMapper->toEntity($member),
                sprintf("Cannot add a user with the type '%s' to the group", $member->getType()));
        }

        /** @var Group $entity */
        $entity = $this->get($group->getId());
        $entity->addMember($this->userRepository->find($member->getId()));

        $this->em->merge($entity);
        $this->flush($flush);

        $this->logger->info("Member added", array ("group" => $entity));

        return $member;
    }


    /**
     * @inheritdoc
     */
    public function removeMember(GroupDto $group, UserDto $member, bool $flush = true) : void
    {
        $this->logger->debug("Removing a member from a group", array ("group" => $group, "user" => $member));

        if ($member->getId() == $group->getCreatorId())
        {
            throw new InvalidInviteeException($this->userDtoMapper->toEntity($member),
                "Cannot remove the group creator from its group");
        }

        /** @var Group $entity */
        $entity = $this->get($group->getId());

        if ($entity->getMembers()->filter(function (User $u) use ($member) {
            return $u->getId() == $member->getId();
        })->isEmpty())
        {
            throw new EntityNotFoundException($member->getEntityClass(), "id", $member->getId());
        }

        $this->logger->debug("Member to remove found in the group");

        /** @var User $userEntity */
        $userEntity = $this->userRepository->find($member->getId());
        $entity->removeMember($userEntity);
        $this->em->merge($entity);
        $this->flush($flush);

        $this->logger->debug("Member removed", array ("group" => $group));
    }


    /**
     * @inheritdoc
     */
    public function hasMember(GroupDto $group, UserDto $user) : bool
    {
        $this->logger->debug("Testing if a group has the user as a member",
            array ("group" => $group, "user" => $user));

        /** @var Group $entity */
        $entity = $this->get($group->getId());
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
    public function uploadGroupPicture(GroupDto $group, File $file, bool $flush = true) : GroupPictureDto
    {
        $this->logger->debug("Uploading a picture for a group",
            array ("group" => $group, "file" => $file, "flush" => $flush));

        /** @var GroupPictureDto $pictureDto */
        $pictureDto = $this->formValidator->validatePictureDtoForm(
            empty($group->getPicture()) ? new GroupPictureDto() : $group->getPicture(),
            $file, GroupPictureDto::class);

        /** @var GroupPicture $picture */
        $picture = $this->pictureDtoMapper->toEntity($pictureDto);
        /** @var Group $entity */
        $entity = $this->dtoMapper->toEntity($group);
        $entity->setPicture($picture);

        empty($picture->getId()) ? $this->em->persist($picture) : $this->em->merge($picture);
        $this->em->merge($entity);
        $this->flush($flush);

        $this->logger->info("Group picture uploaded", array ("picture" => $picture));

        return $this->pictureDtoMapper->toDto($picture);
    }


    /**
     * @inheritdoc
     */
    public function deleteGroupPicture(GroupDto $group, bool $flush = true) : void
    {
        $this->logger->debug("Deleting a group picture", array ("group" => $group));

        /** @var Group $entity */
        $entity = $this->dtoMapper->toEntity($group);

        if (empty($entity->getPicture()))
        {
            return;
        }

        /** @var GroupPicture $picture */
        $picture = $this->em->find(GroupPicture::class, $entity->getPicture()->getId());

        $this->logger->debug("Picture exists for the group", array ("group" => $group, "picture" => $picture));

        $entity->setPicture(null);

        $this->em->remove($picture);
        $this->em->merge($entity);
        $this->flush($flush);

        $this->logger->debug("Group picture deleted");
    }


    /**
     * @inheritdoc
     */
    protected function getDomainClass() : string
    {
        return Group::class;
    }

}