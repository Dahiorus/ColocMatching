<?php

namespace App\Core\Manager\Alert;

use App\Core\DTO\Alert\AlertDto;
use App\Core\DTO\User\UserDto;
use App\Core\Exception\InvalidFormException;
use App\Core\Manager\DtoManagerInterface;
use App\Core\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\ORMException;

interface AlertDtoManagerInterface extends DtoManagerInterface
{
    /**
     * Gets alerts of a user with paging
     *
     * @param UserDto $user The user owning the alert
     * @param Pageable $pageable [optional] Paging information
     *
     * @return AlertDto[]
     */
    public function findByUser(UserDto $user, Pageable $pageable = null) : array;


    /**
     * Counts all alerts of a user
     *
     * @param UserDto $user The user
     *
     * @return int
     * @throws ORMException
     */
    public function countByUser(UserDto $user) : int;


    /**
     * Creates an alert for the user
     *
     * @param UserDto $user The user
     * @param string $filterClass The Searchable filter form class to use to validate the filter
     * @param array $data The data to validate
     * @param bool $flush If the operation must be flushed
     *
     * @return AlertDto
     * @throws InvalidFormException
     */
    public function create(UserDto $user, string $filterClass, array $data, bool $flush = true) : AlertDto;


    /**
     * Updates an existing alert
     *
     * @param AlertDto $alert The alert to update
     * @param array $data The alert new data
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     * @param bool $flush If the operation must be flushed
     *
     * @return AlertDto
     * @throws InvalidFormException
     */
    public function update(AlertDto $alert, array $data, bool $clearMissing, bool $flush = true) : AlertDto;

}
