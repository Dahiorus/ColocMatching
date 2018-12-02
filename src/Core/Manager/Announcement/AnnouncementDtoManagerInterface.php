<?php

namespace App\Core\Manager\Announcement;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\Announcement\AnnouncementPictureDto;
use App\Core\DTO\Announcement\CommentDto;
use App\Core\DTO\Collection;
use App\Core\DTO\Page;
use App\Core\DTO\User\UserDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidCreatorException;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidInviteeException;
use App\Core\Manager\DtoManagerInterface;
use App\Core\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\File\File;

interface AnnouncementDtoManagerInterface extends DtoManagerInterface
{
    /**
     * Finds an announcement having the user as a candidate
     *
     * @param UserDto $candidate The announcement candidate
     *
     * @return AnnouncementDto|null
     * @throws ORMException
     */
    public function findByCandidate(UserDto $candidate);


    /**
     * Creates a new Announcement for a user. The user becomes a proposal user.
     *
     * @param UserDto $user The creator of the announcement
     * @param array $data The data of the new Announcement
     * @param bool $flush If the operation must be flushed
     *
     * @return AnnouncementDto
     * @throws InvalidCreatorException
     * @throws InvalidFormException
     */
    public function create(UserDto $user, array $data, bool $flush = true) : AnnouncementDto;


    /**
     * Updates an existing Announcement
     *
     * @param AnnouncementDto $announcement The announcement to update
     * @param array $data The new data to persist
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     * @param bool $flush If the operation must be flushed
     *
     * @return AnnouncementDto
     * @throws InvalidFormException
     */
    public function update(AnnouncementDto $announcement, array $data, bool $clearMissing,
        bool $flush = true) : AnnouncementDto;


    /**
     * Gets an announcement candidates
     *
     * @param AnnouncementDto $announcement The announcement
     *
     * @return UserDto[]
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getCandidates(AnnouncementDto $announcement) : array;


    /**
     * Adds a candidate to an announcement
     *
     * @param AnnouncementDto $announcement The announcement
     * @param UserDto $candidate The candidate to add
     * @param bool $flush If the operation must be flushed
     *
     * @return UserDto
     * @throws InvalidInviteeException
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function addCandidate(AnnouncementDto $announcement, UserDto $candidate, bool $flush = true) : UserDto;


    /**
     * Removes a candidate from an announcement
     *
     * @param AnnouncementDto $announcement The announcement
     * @param UserDto $candidate The candidate to remove
     * @param bool $flush If he operation must be flushed
     *
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function removeCandidate(AnnouncementDto $announcement, UserDto $candidate, bool $flush = true) : void;


    /**
     * Tests if an announcement has a user as a candidate
     *
     * @param AnnouncementDto $announcement The announcement
     * @param UserDto $user The user
     *
     * @return bool
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function hasCandidate(AnnouncementDto $announcement, UserDto $user) : bool;


    /**
     * Gets the comments of an announcement with pagination
     *
     * @param AnnouncementDto $announcement The announcement having the comments
     * @param Pageable $pageable [optional] Paging information
     *
     * @return Collection|Page
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getComments(AnnouncementDto $announcement, Pageable $pageable = null);


    /**
     * Creates a new comment for an announcement
     *
     * @param AnnouncementDto $announcement The commented announcement
     * @param UserDto $author The author of the comment
     * @param array $data The data of the comment
     * @param bool $flush If the operation must be flushed
     *
     * @return CommentDto
     * @throws InvalidFormException
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function createComment(AnnouncementDto $announcement, UserDto $author, array $data,
        bool $flush = true) : CommentDto;


    /**
     * Deletes a comment from an announcement
     *
     * @param AnnouncementDto $announcement The announcement having the comment to delete
     * @param CommentDto $comment The comment to delete
     * @param bool $flush If the operation must be flushed
     *
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function deleteComment(AnnouncementDto $announcement, CommentDto $comment, bool $flush = true) : void;


    /**
     * Uploads a picture for an announcement
     *
     * @param AnnouncementDto $announcement The announcement
     * @param File $file The picture file to upload
     * @param bool $flush If the operation must be flushed
     *
     * @return AnnouncementPictureDto
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function uploadAnnouncementPicture(AnnouncementDto $announcement, File $file,
        bool $flush = true) : AnnouncementPictureDto;


    /**
     * Deletes a picture of an announcement
     *
     * @param AnnouncementDto $announcement The announcement having the picture
     * @param AnnouncementPictureDto $picture The picture to delete
     * @param bool $flush If the operation must be flushed
     *
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function deleteAnnouncementPicture(AnnouncementDto $announcement, AnnouncementPictureDto $picture,
        bool $flush = true) : void;

}
