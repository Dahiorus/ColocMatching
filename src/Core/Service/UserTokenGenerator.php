<?php

namespace App\Core\Service;

use App\Core\Entity\User\UserToken;

class UserTokenGenerator
{
    public function generateToken(string $username, string $reason) : UserToken
    {
        try
        {
            $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

            return new UserToken($token, $username, $reason);
        }
        catch (\Exception $e)
        {
            throw new \RuntimeException("Unable to create a user token for [$username]", 0, $e);
        }
    }
}