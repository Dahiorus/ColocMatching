<?php

namespace ColocMatching\CoreBundle\Manager\Group;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Group\GroupPicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Manager\ManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

interface GroupManagerInterface extends ManagerInterface {

    /**
     * Creates a new group for a user
     *
     * @param User $user  The creator of the group
     * @param array $data The data of the group
     *
     * @return Group
     * @throws UnprocessableEntityHttpException
     * @throws InvalidFormDataException
     */
    public function create(User $user, array $data) : Group;


    /**
     * Updates an existing group
     *
     * @param Group $group       The group to update
     * @param array $data        The new data to persist
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     *
     * @return Group
     * @throws InvalidFormDataException
     */
    public function update(Group $group, array $data, bool $clearMissing) : Group;


    /**
     * Deletes an existing group
     *
     * @param Group $group The group to delete
     */
    public function delete(Group $group);


    /**
     * Searches groups corresponding to the filter
     *
     * @param GroupFilter $filter The search filter
     * @param array $fields       The fields to return
     *
     * @return array
     */
    public function search(GroupFilter $filter, array $fields = null) : array;


    /**
     * Counts groups corresponding to the filter
     *
     * @param GroupFilter $filter The search filter
     *
     * @return int
     */
    public function countBy(GroupFilter $filter) : int;


    /**
     * Adds a new user in the group
     *
     * @param Group $group The group where adding the user
     * @param User $user   The user to add
     *
     * @return Collection
     * @throws UnprocessableEntityHttpException
     */
    public function addMember(Group $group, User $user) : Collection;


    /**
     * Removes a user from a group
     *
     * @param Group $group The group from where removing the user
     * @param int $userId  The ID of the user to delete
     *
     * @throws UnprocessableEntityHttpException
     */
    public function removeMember(Group $group, int $userId);


    /**
     * Uploads a picture for a group
     *
     * @param Group $group The group receiving the picture
     * @param File $file   The picture to upload
     *
     * @return GroupPicture
     */
    public function uploadGroupPicture(Group $group, File $file) : GroupPicture;


    /**
     * Deletes the picture of an existing group
     *
     * @param Group $group The group from which deleting the picture
     */
    public function deleteGroupPicture(Group $group);


    /**
     * Finds one group having a specific member
     *
     * @param User $member The member of the group
     *
     * @return Group|null
     */
    public function findByMember(User $member);
}