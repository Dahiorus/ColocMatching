<?php

namespace ColocMatching\CoreBundle\Manager\User;

use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\ManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use Symfony\Component\HttpFoundation\File\File;

interface UserManagerInterface extends ManagerInterface {


    /**
     * Search users corresponding to the filter
     *
     * @param UserFilter $filter
     * @param array $fields
     * @return array
     */
    public function search(UserFilter $filter, array $fields = null): array;


    /**
     * Find a User by username
     *
     * @param string $username
     * @return User|null
     * @throws UserNotFoundException
     */
    public function findByUsername(string $username): User;


    /**
     * Create a new User from the POST data
     *
     * @param array $data The data of the new User
     * @return User
     * @throws InvalidFormDataException
     */
    public function create(array $data): User;


    /**
     * Update an existing User from the PUT data
     *
     * @param User $user The User to update
     * @param array $data The new data to persist
     * @return User
     * @throws InvalidFormDataException
     */
    public function update(User $user, array $data): User;


    /**
     * Delete a User
     *
     * @param User $user The User to delete
     */
    public function delete(User $user);


    /**
     * Update an existing User from the PATCH data
     *
     * @param User $user The User to update
     * @param array $data The new data to persist
     * @return User
     * @throws InvalidFormDataException
     */
    public function partialUpdate(User $user, array $data): User;


    /**
     * Upload a profile picture for a User
     *
     * @param User $user The User to set the picture
     * @param File $file The file to upload
     * @throws InvalidFormDataException
     * @return User
     */
    public function uploadProfilePicture(User $user, File $file): ProfilePicture;


    /**
     * Delete a User's profile picture
     *
     * @param User $user The User to delete the picture
     */
    public function deleteProfilePicture(User $user);


    /**
     * Update the profile of a User from the PUT data
     *
     * @param User $user The User to update the profile
     * @param array $data The new data to set
     * @return Profile
     * @throws InvalidFormDataException
     */
    public function updateProfile(User $user, array $data): Profile;


    /**
     * Update the profile of a User from the PATCH data
     *
     * @param User $user The User to update the profile
     * @param array $data The new data to set
     * @return Profile
     * @throws InvalidFormDataException
     */
    public function partialUpdateProfile(User $user, array $data): Profile;

}