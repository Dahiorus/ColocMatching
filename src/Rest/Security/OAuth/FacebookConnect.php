<?php

namespace App\Rest\Security\OAuth;

use App\Core\DTO\User\UserDto;
use App\Core\Exception\InvalidCredentialsException;
use App\Rest\Exception\OAuthConfigurationError;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\FacebookResponse;
use Facebook\GraphNodes\GraphUser;

class FacebookConnect extends OAuthConnect
{
    /**
     * @var Facebook
     */
    private $facebook;


    public function handleAccessToken(string $accessToken) : UserDto
    {
        $this->logger->debug("Handling a user Facebook access token");

        try
        {
            $endpoint = sprintf("/me?fields=%s",
                implode(",", array ("email", "first_name", "last_name", "picture.type(large)")));

            $this->logger->debug("Requesting [GET $endpoint] to get the Facebook user");

            /** @var FacebookResponse $response */
            $response = $this->facebook->get($endpoint, $accessToken);
            /** @var GraphUser $fbUser */
            $fbUser = $response->getGraphUser();

            $data = array (
                self::EXTERNAL_ID => $fbUser->getId(),
                self::EMAIL => $fbUser->getEmail(),
                self::FIRST_NAME => $fbUser->getFirstName(),
                self::LAST_NAME => $fbUser->getLastName(),
                self::PICTURE => $fbUser->getPicture()->getUrl());
            $dto = $this->userDtoMapper->toDto($this->convertUser($data));

            $this->logger->info("User authenticated", array ("user" => $dto));

            return $dto;
        }
        catch (FacebookSDKException $e)
        {
            $this->logger->error("Unable to request '/me' on the Facebook provider",
                array ("exception" => $e));

            throw new InvalidCredentialsException($e->getMessage(), $e);
        }
    }


    public function getProviderName() : string
    {
        return "facebook";
    }


    public function createClient(array $config)
    {
        try
        {
            $this->facebook = new Facebook($config);
        }
        catch (FacebookSDKException $e)
        {
            $this->logger->critical("Unable to create a Facebook instance from the configuration", $config);

            throw new OAuthConfigurationError($this->getProviderName(), $e);
        }
    }

}
