<?php

namespace App\Core\Security\User;

use App\Core\DTO\User\UserDto;
use App\Core\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

interface TokenEncoderInterface
{
    /**
     * Creates a authentication token for the user
     *
     * @param UserDto $user The authenticated user
     *
     * @return string
     */
    public function encode(UserDto $user) : string;


    /**
     * Decodes the authentication token from the request and gets the authenticated user. Can return null if there is
     * no token.
     *
     * @param Request $request The request containing the token
     *
     * @return UserDto|null
     * @throws EntityNotFoundException
     */
    public function decode(Request $request);
}