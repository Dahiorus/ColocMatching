<?php

namespace App\Tests\Rest\Security\OAuth;

use App\Core\DTO\User\UserDto;
use App\Rest\Security\OAuth\OAuthConnect;

class DummyConnect extends OAuthConnect
{
    /** @var array */
    private $client;


    public function handleAccessToken(string $accessToken, string $userPassword = null) : UserDto
    {
        $this->logger->debug("Handling a user Dummy access token");

        $clientUser = $this->client["user"];

        $data = array (
            self::EXTERNAL_ID => $clientUser["id"],
            self::EMAIL => $clientUser["mail"],
            self::FIRST_NAME => $clientUser["givenName"],
            self::LAST_NAME => $clientUser["sn"],
            self::PICTURE => $clientUser["photoUrl"],
            self::USER_PASSWORD => $userPassword,
        );
        $user = $this->userDtoMapper->toDto($this->convertUser($data));

        $this->logger->info("Dummy user handled", array ("user" => $user));

        return $user;
    }


    public function getProviderName() : string
    {
        return "dummy";
    }


    public function createClient(array $config)
    {
        $this->client = array_merge(array (
            "user" => array (
                "id" => null,
                "givenName" => null,
                "sn" => null,
                "mail" => null,
                "photoUrl" => null,
            ),
        ), $config);
    }

}
