<?php

namespace App\Core\Manager\User;

use App\Core\DTO\User\UserDto;
use App\Core\DTO\User\UserTokenDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidParameterException;

interface UserTokenDtoManagerInterface
{
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
    public function create(UserDto $user, string $reason, \DateTimeImmutable $expirationDate,
        bool $flush = true) : UserTokenDto;


    /**
     * Finds a user token matching the specified token
     *
     * @param string $token The token value
     * @param string $reason [optional] The token reason
     *
     * @return UserTokenDto|null
     * @throws EntityNotFoundException
     */
    public function findByToken(string $token, string $reason = null);


    /**
     * Finds a user token for the specified email and reason
     *
     * @param string $email The email
     * @param string $reason The token reason
     *
     * @return UserTokenDto|null
     */
    public function findOneFor(string $email, string $reason);


    /**
     * Deletes a user token
     *
     * @param UserTokenDto $userToken The user token
     * @param bool $flush If the operation must be flushed
     */
    public function delete(UserTokenDto $userToken, bool $flush = true) : void;


    /**
     * Deletes all user tokens
     */
    public function deleteAll() : void;

}
