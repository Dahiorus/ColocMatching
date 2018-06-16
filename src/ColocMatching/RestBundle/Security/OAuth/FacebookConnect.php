<?php

namespace ColocMatching\RestBundle\Security\OAuth;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\InvalidCredentialsException;
use ColocMatching\RestBundle\Exception\OAuthConfigurationError;
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
            $endpoint = sprintf("/me?fields=%s", implode(",", array ("email", "first_name", "last_name")));

            $this->logger->debug("Requesting [GET $endpoint] to get the Facebook user");

            /** @var FacebookResponse $response */
            $response = $this->facebook->get($endpoint, $accessToken);
            /** @var GraphUser $fbUser */
            $fbUser = $response->getGraphUser();

            $data = array (
                self::EXTERNAL_ID => $fbUser->getId(),
                self::EMAIL => $fbUser->getEmail(),
                self::FIRST_NAME => $fbUser->getFirstName(),
                self::LAST_NAME => $fbUser->getLastName());
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
