<?php

namespace ColocMatching\CoreBundle\Manager\Group;

use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\Group\GroupPictureDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidCreatorException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidInviteeException;
use ColocMatching\CoreBundle\Manager\DtoManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\File\File;

interface GroupDtoManagerInterface extends DtoManagerInterface
{
    /**
     * Finds one group having a specific member
     *
     * @param UserDto $member The member of the group
     *
     * @return GroupDto|null
     * @throws ORMException
     */
    public function findByMember(UserDto $member);


    /**
     * Creates a new group for a user
     *
     * @param UserDto $user The creator of the group
     * @param array $data The data of the group
     * @param bool $flush If the operation must be flushed
     *
     * @return GroupDto
     * @throws InvalidCreatorException
     * @throws InvalidFormException
     */
    public function create(UserDto $user, array $data, bool $flush = true) : GroupDto;


    /**
     * Updates a group
     *
     * @param GroupDto $group The group to update
     * @param array $data The new data to persist
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     * @param bool $flush If the operation must be flushed
     *
     * @return GroupDto
     * @throws InvalidFormException
     */
    public function update(GroupDto $group, array $data, bool $clearMissing, bool $flush = true) : GroupDto;


    /**
     * Gets a group members
     *
     * @param GroupDto $group The group
     *
     * @return UserDto[]
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getMembers(GroupDto $group) : array;


    /**
     * Adds a new user in the group
     *
     * @param GroupDto $group The group where adding the user
     * @param UserDto $member The member to add
     * @param bool $flush If the operation must be flushed
     *
     * @return UserDto
     * @throws InvalidInviteeException
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function addMember(GroupDto $group, UserDto $member, bool $flush = true) : UserDto;


    /**
     * Removes a user from a group
     *
     * @param GroupDto $group The group from where removing the user
     * @param UserDto $member The member to remove
     * @param bool $flush If the operation must be flushed
     *
     * @throws InvalidInviteeException
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function removeMember(GroupDto $group, UserDto $member, bool $flush = true) : void;


    /**
     * Tests if a group has a user as a member
     *
     * @param GroupDto $group The group
     * @param UserDto $user The user
     *
     * @return bool
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function hasMember(GroupDto $group, UserDto $user) : bool;


    /**
     * Uploads a picture for a group
     *
     * @param GroupDto $group The group receiving the picture
     * @param File $file The picture to upload
     * @param bool $flush If the operation must be flushed
     *
     * @return GroupPictureDto
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function uploadGroupPicture(GroupDto $group, File $file, bool $flush = true) : GroupPictureDto;


    /**
     * Deletes the picture of an existing group
     *
     * @param GroupDto $group The group from which deleting the picture
     * @param bool $flush If the operation must be flushed
     *
     * @throws ORMException
     */
    public function deleteGroupPicture(GroupDto $group, bool $flush = true) : void;
}