<?php

namespace ColocMatching\CoreBundle\Service;

use ColocMatching\CoreBundle\Entity\User\UserToken;

class UserTokenGenerator
{
    public function generateToken(string $username, string $reason) : UserToken
    {
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        return new UserToken($token, $username, $reason);
    }
}