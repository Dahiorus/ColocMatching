<?php

namespace ColocMatching\CoreBundle\Manager\Group;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\Group\GroupPictureDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Group\GroupPicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\InvalidCreatorException;
use ColocMatching\CoreBundle\Exception\InvalidInviteeException;
use ColocMatching\CoreBundle\Form\Type\Group\GroupDtoForm;
use ColocMatching\CoreBundle\Manager\AbstractDtoManager;
use ColocMatching\CoreBundle\Mapper\Group\GroupDtoMapper;
use ColocMatching\CoreBundle\Mapper\Group\GroupPictureDtoMapper;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Repository\Group\GroupRepository;
use ColocMatching\CoreBundle\Validator\FormValidator;
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

    /** @var UserDtoMapper */
    private $userDtoMapper;

    /** @var GroupPictureDtoMapper */
    private $pictureDtoMapper;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, GroupDtoMapper $dtoMapper,
        FormValidator $formValidator, UserDtoMapper $userDtoMapper, GroupPictureDtoMapper $pictureDtoMapper)
    {
        parent::__construct($logger, $em, $dtoMapper);

        $this->formValidator = $formValidator;
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
        $userEntity = $this->em->getReference(User::class, $member->getId());
        /** @var Group $group */
        $group = $this->repository->findOneByMember($userEntity);

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
            throw new InvalidCreatorException(sprintf("The user '%s' already has a group",
                $userEntity->getUsername()));
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

        $this->em->merge($updatedGroup);
        $this->flush($flush);

        return $this->dtoMapper->toDto($updatedGroup);
    }


    /**
     * @inheritdoc
     */
    public function delete(AbstractDto $dto, bool $flush = true) : void
    {
        // we have to get the entity corresponding to the DTO
        /** @var Group $entity */
        $entity = $this->repository->find($dto->getId());

        $this->logger->debug("Deleting an entity",
            array ("domainClass" => $this->getDomainClass(), "id" => $dto->getId(), "flush" => $flush));

        // removing the relationship between the group to delete and its creator
        $creator = $entity->getCreator();
        $creator->setGroup(null);

        $this->em->merge($creator);
        $this->em->remove($entity);
        $this->flush($flush);
    }


    /**
     * @inheritdoc
     */
    public function getMembers(GroupDto $group) : array
    {
        $this->logger->debug("Getting a group members", array ("group" => $group));

        /** @var Group $entity */
        $entity = $this->get($group->getId());

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
        $entity->addMember($this->em->getReference(User::class, $member->getId()));

        $this->em->merge($entity);
        $this->flush($flush);

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

        if (!$entity->getMembers()->filter(function (User $u) use ($member) {
            return $u->getId() == $member->getId();
        })->isEmpty())
        {
            $this->logger->debug("Member to remove found in the group");

            /** @var User $userEntity */
            $userEntity = $this->em->getReference(User::class, $member->getId());
            $entity->removeMember($userEntity);
            $this->em->merge($entity);
            $this->flush($flush);
        }
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
    }


    /**
     * @inheritdoc
     */
    protected function getDomainClass() : string
    {
        return Group::class;
    }

}