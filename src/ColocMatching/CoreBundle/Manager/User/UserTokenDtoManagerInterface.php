<?php

namespace ColocMatching\CoreBundle\Manager\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\User\UserTokenDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;

interface UserTokenDtoManagerInterface
{
    /**
     * Creates a user token for the user
     *
     * @param UserDto $user The user
     * @param string $reason The reason to create the token
     * @param bool $flush If the operation must be flushed
     *
     * @return UserTokenDto
     * @throws InvalidParameterException
     */
    public function create(UserDto $user, string $reason, bool $flush = true) : UserTokenDto;


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
     * Deletes a user token
     *
     * @param UserTokenDto $userToken The user token
     * @param bool $flush If the operation must be flushed
     *
     * @throws EntityNotFoundException
     */
    public function delete(UserTokenDto $userToken, bool $flush = true) : void;


    /**
     * Deletes all user tokens
     */
    public function deleteAll() : void;

}
