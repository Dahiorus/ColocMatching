<?php

namespace ColocMatching\CoreBundle\Manager\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;
use ColocMatching\CoreBundle\Entity\Announcement\Comment;
use ColocMatching\CoreBundle\Entity\Announcement\Housing;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\AnnouncementPictureNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidCreatorException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidInviteeException;
use ColocMatching\CoreBundle\Manager\ManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;

interface AnnouncementManagerInterface extends ManagerInterface {

    /**
     * Creates a new Annoucement for a user
     *
     * @param User $user  The creator of the annoucement
     * @param array $data The data of the new Announcement
     *
     * @return Announcement
     * @throws InvalidCreatorException
     * @throws InvalidFormException
     */
    public function create(User $user, array $data) : Announcement;


    /**
     * Updates an existing Announcement
     *
     * @param Announcement $announcement The Announcement to update
     * @param array $data                The new data to persist
     * @param bool $clearMissing         Indicates that if missing data are considered as null value
     *
     * @return Announcement
     * @throws InvalidFormException
     */
    public function update(Announcement $announcement, array $data, bool $clearMissing) : Announcement;


    /**
     * Deletes an existing Announcement
     *
     * @param Announcement $announcement The Announcement to delete
     */
    public function delete(Announcement $announcement);


    /**
     * Searches announcements corresponding to the filter
     *
     * @param AnnouncementFilter $filter The search filter
     * @param array $fields              The fields to return
     *
     * @return array
     */
    public function search(AnnouncementFilter $filter, array $fields = null) : array;


    /**
     * Counts instances corresponding to the filter
     *
     * @param AnnouncementFilter $filter The search filter
     *
     * @return int
     */
    public function countBy(AnnouncementFilter $filter) : int;


    /**
     * Uploads a picture for an existing Announcement
     *
     * @param Announcement $announcement The Announcement to upload the picture
     * @param File $file                 The picture to upload
     *
     * @return Collection of AnnouncementPicture
     * @throws InvalidFormException
     */
    public function uploadAnnouncementPicture(Announcement $announcement, File $file) : Collection;


    /**
     * Gets an existing picture from an Announcement
     *
     * @param Announcement $announcement The Announcement to get the picture
     * @param int $pictureId             The picture id
     *
     * @return AnnouncementPicture
     * @throws AnnouncementPictureNotFoundException
     */
    public function readAnnouncementPicture(Announcement $announcement, int $pictureId) : AnnouncementPicture;


    /**
     * Deletes a picture of an existing Announcement
     *
     * @param AnnouncementPicture $picture The picture to delete
     */
    public function deleteAnnouncementPicture(AnnouncementPicture $picture);


    /**
     * Adds a user to the list of canditates of an exisitng Announcement
     *
     * @param Announcement $announcement
     * @param User $user
     *
     * @return Collection of User
     * @throws InvalidInviteeException
     */
    public function addCandidate(Announcement $announcement, User $user) : Collection;


    /**
     * Removes a candidate from the list of candidates of an existing Announcement
     *
     * @param Announcement $announcement
     * @param int $userId The Id of the candidate to remove
     */
    public function removeCandidate(Announcement $announcement, int $userId);


    /**
     * Updates the housing of an existing Announcement
     *
     * @param Announcement $announcement The Announcement to update the Housing
     * @param array $data                The housing data to persist
     * @param bool $clearMissing         Indicates that if missing data are considered as null value
     *
     * @return Housing
     * @throws InvalidFormException
     */
    public function updateHousing(Announcement $announcement, array $data, bool $clearMissing) : Housing;


    /**
     * Finds an announcement having the user as a candidate
     *
     * @param User $candidate The candidate of the announcement
     *
     * @return Announcement|null
     */
    public function findByCandidate(User $candidate);


    /**
     * Gets the comments of an announcement with pagination
     *
     * @param Announcement $announcement The announcement from witch get the comments
     * @param PageableFilter $filter     Pagination information
     *
     * @return array
     */
    public function getComments(Announcement $announcement, PageableFilter $filter) : array;


    /**
     * Creates a new comment for an announcement
     *
     * @param Announcement $announcement The commented announcement
     * @param User $author               The author of the comment
     * @param array $data                The data of the comment
     *
     * @return Comment
     * @throws InvalidFormException
     */
    public function createComment(Announcement $announcement, User $author, array $data) : Comment;


    /**
     * Deletes a comment from an announcement
     *
     * @param Announcement $announcement The announcement from which deleting the comment
     * @param int $id                    The comment identifier
     */
    public function deleteComment(Announcement $announcement, int $id);

}