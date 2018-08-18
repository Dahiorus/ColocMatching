<?php

namespace App\Core\Manager\User;

use App\Core\DTO\User\AnnouncementPreferenceDto;
use App\Core\DTO\User\ProfileDto;
use App\Core\DTO\User\ProfilePictureDto;
use App\Core\DTO\User\UserDto;
use App\Core\DTO\User\UserPreferenceDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Manager\DtoManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\File\File;

interface UserDtoManagerInterface extends DtoManagerInterface
{
    /**
     * Finds a User by username
     *
     * @param string $username The user username
     *
     * @return UserDto
     * @throws EntityNotFoundException
     */
    public function findByUsername(string $username) : UserDto;


    /**
     * Creates a new User
     *
     * @param array $data The data of the new User
     * @param bool $flush If the operation must be flushed
     *
     * @return UserDto
     * @throws InvalidFormException
     */
    public function create(array $data, bool $flush = true) : UserDto;


    /**
     * Updates an existing User
     *
     * @param UserDto $user The User to update
     * @param array $data The new data to persist
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     * @param bool $flush If the operation must be flushed
     *
     * @return UserDto
     * @throws InvalidFormException
     */
    public function update(UserDto $user, array $data, bool $clearMissing, bool $flush = true) : UserDto;


    /**
     * Updates the status of a user
     *
     * @param UserDto $user The user to update the status
     * @param string $status The status value
     * @param bool $flush If the operation must be flushed
     *
     * @return UserDto
     * @throws InvalidParameterException
     * @throws ORMException
     */
    public function updateStatus(UserDto $user, string $status, bool $flush = true) : UserDto;


    /**
     * Updates the password of a user
     *
     * @param UserDto $user The user to update the password
     * @param array $data The data containing the new password
     * @param bool $flush If the operation must be flushed
     *
     * @return UserDto
     * @throws InvalidFormException
     */
    public function updatePassword(UserDto $user, array $data, bool $flush = true) : UserDto;


    /**
     * Uploads a profile picture for a User
     *
     * @param UserDto $user The user to set the picture
     * @param File $file The file to upload
     * @param bool $flush If the operation must be flushed
     *
     * @return ProfilePictureDto
     * @throws InvalidFormException
     */
    public function uploadProfilePicture(UserDto $user, File $file, bool $flush = true) : ProfilePictureDto;


    /**
     * Deletes a User's profile picture
     *
     * @param UserDto $user The User to delete the picture
     * @param bool $flush If the operation must be flushed
     */
    public function deleteProfilePicture(UserDto $user, bool $flush = true) : void;


    /**
     * Gets the user's profile
     *
     * @param UserDto $user The user
     *
     * @return ProfileDto
     */
    public function getProfile(UserDto $user) : ProfileDto;


    /**
     * Updates the profile of a User
     *
     * @param UserDto $user The User to update the profile
     * @param array $data The new data to set
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     * @param bool $flush If the operation must be flushed
     *
     * @return ProfileDto
     * @throws InvalidFormException
     */
    public function updateProfile(UserDto $user, array $data, bool $clearMissing, bool $flush = true) : ProfileDto;


    /**
     * Gets the user's announcement preference
     *
     * @param UserDto $user The user
     *
     * @return AnnouncementPreferenceDto
     */
    public function getAnnouncementPreference(UserDto $user) : AnnouncementPreferenceDto;


    /**
     * Updates the announcement search preference of a User
     *
     * @param UserDto $user The User to update the announcement search preference
     * @param array $data The new data to set
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     * @param bool $flush If the operation must be flushed
     *
     * @return AnnouncementPreferenceDto
     * @throws InvalidFormException
     */
    public function updateAnnouncementPreference(UserDto $user, array $data,
        bool $clearMissing, bool $flush = true) : AnnouncementPreferenceDto;


    /**
     * Gets the user's user preference
     *
     * @param UserDto $user The user
     *
     * @return UserPreferenceDto
     */
    public function getUserPreference(UserDto $user) : UserPreferenceDto;


    /**
     * Updates the user search preference of a User
     *
     * @param UserDto $user The User to update the user search preference
     * @param array $data The new data to set
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     * @param bool $flush If the operation must be flushed
     *
     * @return UserPreferenceDto
     * @throws InvalidFormException
     */
    public function updateUserPreference(UserDto $user, array $data, bool $clearMissing,
        bool $flush = true) : UserPreferenceDto;


    /**
     * Adds a role to the user
     *
     * @param UserDto $user The user
     * @param string $role The role to add
     * @param bool $flush If the operation must be flushed
     *
     * @return UserDto
     * @throws ORMException
     * @throws EntityNotFoundException
     */
    public function addRole(UserDto $user, string $role, bool $flush = true) : UserDto;

}
