<?php

namespace ColocMatching\CoreBundle\Manager\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Manager\ManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use Symfony\Component\HttpFoundation\File\File;
use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

interface AnnouncementManagerInterface extends ManagerInterface {


    /**
     * Get Announcements by address
     *
     * @param Address $address The location of the Announcement
     * @param AbstractFilter $filter The pagination filter
     * @param array $fields The fields to return
     * @return array
     */
    public function getByAddress(Address $address, AbstractFilter $filter, array $fileds = null): array;


    /**
     * Count announcement corresponding to the address
     * @param Address $address
     * @return int
     */
    public function countByAddress(Address $address): int;


    /**
     * Create a new Annoucement for a user from the POST data
     *
     * @param User $user The creator of the annoucement
     * @param array $data The data of the new Announcement
     * @return Announcement
     * @throws InvalidFormDataException
     */
    public function create(User $user, array $data): Announcement;


    /**
     * Update an existing Announcement from the PUT data
     *
     * @param Announcement $announcement The Announcement to update
     * @param array $data The new data to persist
     * @return Announcement
     * @throws InvalidFormDataException
     */
    public function update(Announcement $announcement, array $data): Announcement;


    /**
     * Delete an existing Announcement
     *
     * @param Announcement $announcement The Announcement to delete
     */
    public function delete(Announcement $announcement);


    /**
     * Update an existing Announcement from the PATCH data
     *
     * @param Announcement $announcement The Announcement to update
     * @param array $data The new data to persist
     * @return Announcement
     * @throws InvalidFormDataException
     */
    public function partialUpdate(Announcement $announcement, array $data): Announcement;


    /**
     * Upload a picture for an existing Announcement
     *
     * @param Announcement $announcement The Announcement to upload the picture
     * @param File $file The picture to upload
     * @return Announcement
     * @throws InvalidFormDataException
     */
    public function uploadAnnouncementPicture(Announcement $announcement, File $file): Announcement;


    /**
     * Delete a picture of an existing Announcement
     *
     * @param AnnouncementPicture $picture The picture to delete
     */
    public function deleteAnnouncementPicture(AnnouncementPicture $picture);


    /**
     * Add a user to the list of canditates of an exisitng Announcement
     *
     * @param Announcement $announcement
     * @param User $user
     * @return Announcement
     * @throws UnprocessableEntityHttpException
     */
    public function addNewCandidate(Announcement $announcement, User $user): Announcement;


    /**
     * Remove a candidate from the list of candidates of an existing Announcement
     *
     * @param Announcement $announcement
     * @param int $userId The Id of the candidate to remove
     * @return Announcement
     */
    public function removeCandidate(Announcement $announcement, int $userId): Announcement;

}