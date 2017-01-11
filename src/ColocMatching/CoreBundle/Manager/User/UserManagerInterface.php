<?php

namespace ColocMatching\CoreBundle\Manager\User;

use ColocMatching\CoreBundle\Manager\ManagerInterface;
use ColocMatching\CoreBundle\Entity\User\User;

interface UserManagerInterface extends ManagerInterface {
	
	/**
	 * Get a User by username
	 *
	 * @param string $username
	 * @return User|null
	 */
	public function getByUsername(string $username);
	
	
	/**
	 * Create a new User from the POST data
	 *
	 * @param array $data The data of the new User
	 * @return User
	 * @throws InvalidFormDataException
	 */
	public function create(array $data) : User;
	
	
	/**
	 * Update an existing User from the PUT data
	 *
	 * @param User $user The User to update
	 * @param array $data The new data to persist
	 * @return User
	 * @throws InvalidFormDataException
	 */
	public function update(User $user, array $data) : User;
	
	
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
	public function partialUpdate(User $user, array $data) : User;
}