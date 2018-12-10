<?php

namespace App\Rest\Security\OAuth;

use App\Core\DTO\User\UserDto;
use App\Core\Exception\InvalidCredentialsException;

class GoogleConnect extends OAuthConnect
{
    /**
     * @var \Google_Client
     */
    private $googleClient;


    public function handleAccessToken(string $accessToken, string $userPassword = null) : UserDto
    {
        $this->logger->debug("Handling a user Google access token");

        try
        {
            $this->googleClient->setAccessToken($accessToken);
            $oauth2 = new \Google_Service_Oauth2($this->googleClient);

            /** @var \Google_Service_Oauth2_Userinfoplus $userInfo */
            $userInfo = $oauth2->userinfo_v2_me->get();

            $data = array (
                self::EXTERNAL_ID => $userInfo->getId(),
                self::EMAIL => $userInfo->getEmail(),
                self::FIRST_NAME => $userInfo->getGivenName(),
                self::LAST_NAME => $userInfo->getFamilyName(),
                self::PICTURE => $userInfo->getPicture(),
                self::USER_PASSWORD => $userPassword);
            $dto = $this->userDtoMapper->toDto($this->convertUser($data));

            $this->logger->info("Google user handled", array ("user" => $dto));

            return $dto;
        }
        catch (\Google_Service_Exception $e)
        {
            $this->logger->error("Unable to request 'userInfo_v2_me' on the Google provider",
                array ("exception" => $e));

            // get the message of all errors
            $errors = $e->getErrors();
            $errorMessages = array_filter(array_map(function ($error) {
                return (isset($error["message"]) && !empty($error["message"])) ? $error["message"] : null;
            }, $errors), function ($msg) {
                return !empty($msg);
            });

            throw new InvalidCredentialsException("[" . implode(" | ", $errorMessages) . "]", $e);
        }
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
