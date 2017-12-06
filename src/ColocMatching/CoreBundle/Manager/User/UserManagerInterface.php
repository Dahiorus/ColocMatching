<?php

namespace ColocMatching\CoreBundle\Manager\User;

use ColocMatching\CoreBundle\Entity\User\AnnouncementPreference;
use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserPreference;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\ManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use Symfony\Component\HttpFoundation\File\File;

interface UserManagerInterface extends ManagerInterface {

    /**
     * Finds a User by username
     *
     * @param string $username
     *
     * @return User
     * @throws UserNotFoundException
     */
    public function findByUsername(string $username) : User;


    /**
     * Creates a new User
     *
     * @param array $data The data of the new User
     * @param bool $flush If the operation must be flushed
     *
     * @return User
     * @throws InvalidFormException
     */
    public function create(array $data, bool $flush = true) : User;


    /**
     * Updates an existing User
     *
     * @param User $user         The User to update
     * @param array $data        The new data to persist
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     *
     * @return User
     * @throws InvalidFormException
     */
    public function update(User $user, array $data, bool $clearMissing) : User;


    /**
     * Deletes a User
     *
     * @param User $user The User to delete
     */
    public function delete(User $user);


    /**
     * Searches users corresponding to the filter
     *
     * @param UserFilter $filter
     * @param array $fields
     *
     * @return array
     */
    public function search(UserFilter $filter, array $fields = null) : array;


    /**
     * Counts instances corresponding to the filter
     *
     * @param UserFilter $filter The search filter
     *
     * @return int
     */
    public function countBy(UserFilter $filter) : int;


    /**
     * Uploads a profile picture for a User
     *
     * @param User $user The User to set the picture
     * @param File $file The file to upload
     *
     * @throws InvalidFormException
     * @return ProfilePicture
     */
    public function uploadProfilePicture(User $user, File $file) : ProfilePicture;


    /**
     * Deletes a User's profile picture
     *
     * @param User $user The User to delete the picture
     */
    public function deleteProfilePicture(User $user);


    /**
     * Updates the profile of a User
     *
     * @param User $user         The User to update the profile
     * @param array $data        The new data to set
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     *
     * @return Profile
     * @throws InvalidFormException
     */
    public function updateProfile(User $user, array $data, bool $clearMissing) : Profile;


    /**
     * Updates the announcement search preference of a User
     *
     * @param User $user         The User to update the announcement search preference
     * @param array $data        The new data to set
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     *
     * @return AnnouncementPreference
     * @throws InvalidFormException
     */
    public function updateAnnouncementPreference(User $user, array $data, bool $clearMissing) : AnnouncementPreference;


    /**
     * Updates the user search preference of a User
     *
     * @param User $user         The User to update the user search preference
     * @param array $data        The new data to set
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     *
     * @return UserPreference
     * @throws InvalidFormException
     */
    public function updateUserPreference(User $user, array $data, bool $clearMissing) : UserPreference;


    /**
     * Updates the status of a user
     *
     * @param User $user     The user to update the status
     * @param string $status The status value
     *
     * @return User
     * @throws InvalidParameterException
     */
    public function updateStatus(User $user, string $status) : User;


    /**
     * Updates the password of a user
     *
     * @param User $user  The user to update the password
     * @param array $data The data containing the new password
     * @param bool $flush If the operation must be flushed
     *
     * @return User
     * @throws InvalidFormException
     */
    public function updatePassword(User $user, array $data, bool $flush = true) : User;

}
