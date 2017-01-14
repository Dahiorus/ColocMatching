<?php

namespace ColocMatching\CoreBundle\Manager\Announcement;

use ColocMatching\CoreBundle\Manager\ManagerInterface;
use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;

interface AnnouncementManagerInterface extends ManagerInterface {

	/**
	 * Get Announcements by address
	 *
	 * @param Address $address The location of the Announcement
	 * @return array
	 */
	public function getByAddress(Address $address, AbstractFilter $filter) : array;
	
	
	/**
	 * Get Announcements fields by address
	 *
	 * @param Address $address The location of the Announcement
	 * @param array $fields The fields to return
	 * @return array
	 */
	public function getFieldsByAddress(Address $address, array $fields, AbstractFilter $filter) : array;
	
	
	/**
	 * Create a new Annoucement for a user from the POST data
	 *
	 * @param User $user The owner of the annoucement
	 * @param array $data The data of the new Announcement
	 * @return Announcement
	 * @throws InvalidFormDataException
	 */
	public function create(User $user, array $data) : Announcement;
	
	
	/**
	 * Update an existing Announcement from the PUT data
	 *
	 * @param Announcement $announcement The Announcement to update
	 * @param array $data The new data to persist
	 * @return Announcement
	 * @throws InvalidFormDataException
	 */
	public function update(Announcement $announcement, array $data) : Announcement;
	
	
	/**
	 * Delete an existing Announcement
	 *
	 * @param Announcement $announcement The Announcement to delete
	 */
	public function delete(Announcement $announcement);
	
	
	/**
	 * Update an existing Announcement from the PATCH data
	 *
	 * @param Announcement $announcement
	 * @return Announcement
	 * @throws InvalidFormDataException
	 */
	public function partialUpdate(Announcement $announcement, array $data) : Announcement;
	
}