<?php

namespace ColocMatching\RestBundle\Security\OAuth;

use ColocMatching\CoreBundle\DTO\User\UserDto;

class GoogleConnect extends OAuthConnect
{
    /**
     * @var \Google_Client
     */
    private $googleClient;


    public function handleAccessToken(string $accessToken) : UserDto
    {
        $this->logger->debug("Handling a user Google access token");

        $this->googleClient->setAccessToken($accessToken);
        $oauth2 = new \Google_Service_Oauth2($this->googleClient);

        /** @var \Google_Service_Oauth2_Userinfoplus $userInfo */
        $userInfo = $oauth2->userinfo_v2_me->get();

        $data = array (
            self::EXTERNAL_ID => $userInfo->getId(),
            self::EMAIL => $userInfo->getEmail(),
            self::FIRST_NAME => $userInfo->getGivenName(),
            self::LAST_NAME => $userInfo->getFamilyName());
        $dto = $this->userDtoMapper->toDto($this->convertUser($data));

        $this->logger->info("User authenticated", array ("user" => $dto));

        return $dto;
    }


    public function getProviderName() : string
    {
        return "google";
    }


    public function createClient(array $config)
    {
        $this->googleClient = new \Google_Client($config);
    }

}
