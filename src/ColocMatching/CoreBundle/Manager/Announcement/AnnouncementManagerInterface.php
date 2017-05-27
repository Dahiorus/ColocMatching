<?php

namespace ColocMatching\CoreBundle\Manager\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Manager\ManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use ColocMatching\CoreBundle\Entity\Announcement\Housing;
use ColocMatching\CoreBundle\Exception\AnnouncementPictureNotFoundException;

interface AnnouncementManagerInterface extends ManagerInterface {


    /**
     * Creates a new Annoucement for a user from the POST data
     *
     * @param User $user The creator of the annoucement
     * @param array $data The data of the new Announcement
     * @return Announcement
     * @throws InvalidFormDataException
     * @throws UnprocessableEntityHttpException
     */
    public function create(User $user, array $data): Announcement;


    /**
     * Updates an existing Announcement from the PUT data
     *
     * @param Announcement $announcement The Announcement to update
     * @param array $data The new data to persist
     * @return Announcement
     * @throws InvalidFormDataException
     */
    public function update(Announcement $announcement, array $data): Announcement;


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
     * @param array $fields The fields to return
     * @return array
     */
    public function search(AnnouncementFilter $filter, array $fields = null): array;


    /**
     * Counts instances corresponding to the filter
     *
     * @param AnnouncementFilter $filter The search filter
     * @return int
     */
    public function countBy(AnnouncementFilter $filter): int;


    /**
     * Updates an existing Announcement from the PATCH data
     *
     * @param Announcement $announcement The Announcement to update
     * @param array $data The new data to persist
     * @return Announcement
     * @throws InvalidFormDataException
     */
    public function partialUpdate(Announcement $announcement, array $data): Announcement;


    /**
     * Uploads a picture for an existing Announcement
     *
     * @param Announcement $announcement The Announcement to upload the picture
     * @param File $file The picture to upload
     * @return Collection of AnnouncementPicture
     * @throws InvalidFormDataException
     */
    public function uploadAnnouncementPicture(Announcement $announcement, File $file): Collection;


    /**
     * Gets an existing picture from an Announcement
     *
     * @param Announcement $announcement The Announcement to get the picture
     * @param int $pictureId The picture id
     * @return AnnouncementPicture
     * @throws AnnouncementPictureNotFoundException
     */
    public function readAnnouncementPicture(Announcement $announcement, int $pictureId): AnnouncementPicture;


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
     * @return Collection of User
     * @throws UnprocessableEntityHttpException
     */
    public function addNewCandidate(Announcement $announcement, User $user): Collection;


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
     * @param array $data The housing data to persist
     * @return Housing
     */
    public function updateHousing(Announcement $announcement, array $data): Housing;


    /**
     * Updates (partial) the housing of an existing Announcement
     *
     * @param Announcement $announcement The Announcement to update the Housing
     * @param array $data The housing data to persist
     * @return Housing
     */
    public function partialUpdateHousing(Announcement $announcement, array $data): Housing;

}