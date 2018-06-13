<?php

namespace ColocMatching\RestBundle\Security\OAuth;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\ExternalIdentity;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\ORMException;

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
        $user = $this->convertUser($oauth2->userinfo_v2_me->get());
        $dto = $this->userDtoMapper->toDto($user);

        $this->logger->info("User authenticated", array ("user" => $dto));

        return $dto;
    }


    public function getProviderName() : string
    {
        return "google";
    }


    public function createGoogle(array $config)
    {
        $this->googleClient = new \Google_Client($config);
    }


    /**
     * Retrieves a user from the Google user info
     *
     * @param \Google_Service_Oauth2_Userinfoplus $googleUser The Google user info
     *
     * @return User
     * @throws ORMException
     */
    private function convertUser(\Google_Service_Oauth2_Userinfoplus $googleUser)
    {
        $this->logger->debug("Getting a user from the Google user info", array ("googleUser" => $googleUser));

        $googleId = $googleUser->getId();
        /** @var ExternalIdentity $providerId */
        $providerId = $this->providerIdRepository->findOneByProvider($this->getProviderName(), $googleId);

        // the google ID matches a user -> return the user
        if (!empty($providerId))
        {
            $user = $providerId->getUser();

            $this->logger->debug("User exists for the provider", array ("providerId" => $providerId, "user" => $user));

            return $user;
        }

        $email = $googleUser->getEmail();
        /** @var User $user */
        $user = $this->userRepository->findOneBy(array ("email" => $email));

        // no user from the email -> create the user
        if (empty($user))
        {
            $this->logger->debug("No user exists with the e-mail address [$email], creating a new user from the Google user",
                array ("googleUser" => $googleUser));

            $user = new User($email, null, $googleUser->getGivenName(), $googleUser->getFamilyName());
            $this->entityManager->persist($user);
        }

        $this->logger->debug("Creating a Facebook identity for the user", array ("user" => $user));

        // persist the user Facebook ID
        $providerId = new ExternalIdentity($user, $this->getProviderName(), $googleId);
        $this->entityManager->persist($providerId);

        $this->entityManager->flush();

        $this->logger->info("Facebook identity created for the user",
            array ("providerId" => $providerId, "user" => $user));

        return $user;
    }
}