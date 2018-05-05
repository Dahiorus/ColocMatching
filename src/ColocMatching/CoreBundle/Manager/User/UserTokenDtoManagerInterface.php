<?php

namespace ColocMatching\CoreBundle\Manager\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\User\UserTokenDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use Doctrine\ORM\ORMException;

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
     *
     * @return UserTokenDto|null
     * @throws EntityNotFoundException
     */
    public function findByToken(string $token);


    /**
     * Deletes a user token
     *
     * @param UserTokenDto $userToken The user token
     * @param bool $flush If the operation must be flushed
     *
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function delete(UserTokenDto $userToken, bool $flush = true) : void;

}
