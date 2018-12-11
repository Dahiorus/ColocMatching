<?php

namespace App\Core\Exception;

use App\Core\DTO\User\UserTokenDto;
use Throwable;

class ExpiredUserTokenException extends ColocMatchingException
{
    public function __construct(UserTokenDto $userToken, Throwable $previous = null)
    {
        parent::__construct("The user token [$userToken] is expired", 400, $previous);
    }
}