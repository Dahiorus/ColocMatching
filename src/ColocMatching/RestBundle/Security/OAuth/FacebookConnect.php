<?php

namespace ColocMatching\RestBundle\Security\OAuth;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\ExternalIdentity;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidCredentialsException;
use ColocMatching\RestBundle\Exception\OAuthConfigurationError;
use Doctrine\ORM\ORMException;
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
            $endpoint = sprintf("/me?fields=%s", implode(",", $this->fields));

            $this->logger->debug("Requesting [GET $endpoint] to get the Facebook user");

            /** @var FacebookResponse $response */
            $response = $this->facebook->get($endpoint, $accessToken);
            $user = $this->convertUser($response->getGraphUser());
            $dto = $this->userDtoMapper->toDto($user);

            $this->logger->info("User authenticated", array ("user" => $dto));

            return $dto;
        }
        catch (FacebookSDKException $e)
        {
            $this->logger->error("Unable to request '/me' on the Facebook provider",
                array ("accessToken" => $accessToken, "exception" => $e));

            throw new InvalidCredentialsException($e->getMessage(), $e);
        }
    }


    public function getProviderName() : string
    {
        return "facebook";
    }


    /**
     * Creates a Facebook instance with the specified configurations
     *
     * @param array $config The Facebook instance configurations
     *
     * @throws OAuthConfigurationError If unable to create the instance
     */
    public function createFacebook(array $config)
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


    /**
     * Sets the Facebook user's fields to get from the API call
     *
     * @param string[] $fields The fields to get from the Facebook API call
     */
    public function setFields(array $fields = array ())
    {
        // email, firstName, lastName are mandatory
        $this->fields = array_unique(array_merge(array ("email", "first_name", "last_name"), $fields));
    }


    /**
     * Retrieves a user from the Facebook graph user
     *
     * @param GraphUser $fbUser The Facebook graph user
     *
     * @return User
     * @throws ORMException
     */
    private function convertUser(GraphUser $fbUser) : User
    {
        $this->logger->debug("Getting a user from the Facebook graph user", array ("fbUser" => $fbUser));

        $fbId = $fbUser->getId();
        /** @var ExternalIdentity $providerId */
        $providerId = $this->externalIdRepository->findOneByProvider($this->getProviderName(), $fbId);

        // the facebook ID matches a user -> return the user
        if (!empty($providerId))
        {
            $user = $providerId->getUser();

            $this->logger->debug("User exists for the provider", array ("providerId" => $providerId, "user" => $user));

            return $user;
        }

        $email = $fbUser->getEmail();
        /** @var User $user */
        $user = $this->userRepository->findOneBy(array ("email" => $email));

        // no user from the email -> create the user
        if (empty($user))
        {
            $this->logger->debug("No user exists with the e-mail address [$email], creating a new user from the Facebook user",
                array ("fbUser" => $fbUser));

            $user = new User($email, null, $fbUser->getFirstName(), $fbUser->getLastName());
            $this->entityManager->persist($user);
        }

        $this->logger->debug("Creating a Facebook identity for the user", array ("user" => $user));

        // persist the user Facebook ID
        $providerId = new ExternalIdentity($user, $this->getProviderName(), $fbId);
        $this->entityManager->persist($providerId);

        $this->entityManager->flush();

        $this->logger->info("Facebook identity created for the user",
            array ("providerId" => $providerId, "user" => $user));

        return $user;
    }

}
