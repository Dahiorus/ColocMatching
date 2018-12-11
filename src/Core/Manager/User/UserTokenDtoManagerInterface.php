<?php

namespace App\Core\Manager\User;

use App\Core\DTO\User\UserDto;
use App\Core\DTO\User\UserTokenDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidParameterException;
use Doctrine\ORM\ORMException;

interface UserTokenDtoManagerInterface
{
    /**
     * Counts all user tokens expiring before the specified date
     *
     * @param \DateTimeImmutable $expiredSince The date limit
     *
     * @return int
     * @throws ORMException
     */
    public function countAllBefore(\DateTimeImmutable $expiredSince) : int;


    /**
     * Creates a user token for the user
     *
     * @param UserDto $user The user
     * @param string $reason The reason to create the token
     * @param \DateTimeImmutable $expirationDate The token expiration date
     * @param bool $flush If the operation must be flushed
     *
     * @return UserTokenDto
     * @throws InvalidParameterException
     */
    public function createOrUpdate(UserDto $user, string $reason, \DateTimeImmutable $expirationDate,
        bool $flush = true) : UserTokenDto;


    /**
     * Finds a user token matching the specified token
     *
     * @param string $token The token value
     * @param string $reason [optional] The token reason
     *
     * @return UserTokenDto
     * @throws EntityNotFoundException
     */
    public function getByToken(string $token, string $reason = null);


    /**
     * Deletes a user token
     *
     * @param UserTokenDto $userToken The user token
     * @param bool $flush If the operation must be flushed
     */
    public function delete(UserTokenDto $userToken, bool $flush = true) : void;


    /**
     * Deletes all user tokens expiring before the specified date
     *
     * @param \DateTimeImmutable $expiredSince The date limit
     * @param bool $flush If the operation must be flushed
     *
     * @return int
     */
    public function deleteAllBefore(\DateTimeImmutable $expiredSince, bool $flush = true) : int;


    /**
     * Deletes all user tokens
     */
    public function deleteAll() : void;

}
