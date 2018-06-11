<?php

namespace ColocMatching\RestBundle\Security\OAuth;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\ExternalIdentity;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidCredentialsException;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Repository\User\UserRepository;
use ColocMatching\RestBundle\Exception\OAuthConfigurationError;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\FacebookResponse;
use Facebook\GraphNodes\GraphUser;
use Psr\Log\LoggerInterface;

class FacebookConnect extends OAuthConnect
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var Facebook
     */
    private $facebook;


    /**
     * FacebookConnect constructor.
     *
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @param UserDtoMapper $userDtoMapper
     * @param array $scope
     * @param array $facebookConfig
     *
     * @throws OAuthConfigurationError
     */
    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager,
        UserDtoMapper $userDtoMapper, array $scope, array $facebookConfig)
    {
        parent::__construct($logger, $entityManager, $userDtoMapper, $scope);

        $this->userRepository = $entityManager->getRepository(User::class);

        try
        {
            $this->facebook = new Facebook($facebookConfig);
        }
        catch (FacebookSDKException $e)
        {
            $this->logger->critical("Unable to create a Facebook instance from the configuration", $facebookConfig);

            throw new OAuthConfigurationError($this->getProviderName(), $e);
        }
    }


    public function handleAccessToken(string $accessToken) : UserDto
    {
        $this->logger->debug("Handling a user Facebook access token", array ("accessToken" => $accessToken));

        try
        {
            /** @var FacebookResponse $response */
            $response = $this->facebook->get(sprintf("/me?fields=%s", implode(",", $this->scope)), $accessToken);
            $user = $this->getUser($response->getGraphUser());
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
     * Retrieves a user from the Facebook graph user
     *
     * @param GraphUser $fbUser The Facebook graph user
     *
     * @return User
     * @throws ORMException
     */
    private function getUser(GraphUser $fbUser) : User
    {
        $this->logger->debug("Getting a user from the Facebook graph user", array ("fbUser" => $fbUser));

        $fbId = $fbUser->getId();
        /** @var ExternalIdentity $extId */
        $extId = $this->externalIdRepository->findOneByProvider($this->getProviderName(), $fbId);

        // the facebook ID matches a user -> return the user
        if (!empty($extId))
        {
            $user = $extId->getUser();

            $this->logger->debug("User exists for the provider", array ("externalIdentity" => $extId, "user" => $user));

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
        $extId = new ExternalIdentity($user, $this->getProviderName(), $fbId);
        $this->entityManager->persist($extId);

        $this->entityManager->flush();

        $this->logger->info("Facebook identity created for the user",
            array ("externalIdentity" => $extId, "user" => $user));

        return $user;
    }

}
