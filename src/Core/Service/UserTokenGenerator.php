<?php

namespace App\Core\Service;

use App\Core\Entity\User\UserToken;

class UserTokenGenerator
{
    public function generateToken(string $username, string $reason) : UserToken
    {
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        return new UserToken($token, $username, $reason);
    }
}