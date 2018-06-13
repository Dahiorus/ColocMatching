<?php

namespace ColocMatching\RestBundle\Security\OAuth;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\ExternalIdentity;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidCredentialsException;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Repository\User\ExternalIdentityRepository;
use ColocMatching\CoreBundle\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

abstract class OAuthConnect
{
    protected const EXTERNAL_ID = "externalId";
    protected const EMAIL = "email";
    protected const FIRST_NAME = "firstName";
    protected const LAST_NAME = "lastName";

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var ExternalIdentityRepository
     */
    protected $providerIdRepository;

    /**
     * @var UserDtoMapper
     */
    protected $userDtoMapper;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager,
        UserDtoMapper $userDtoMapper)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->providerIdRepository = $entityManager->getRepository(ExternalIdentity::class);
        $this->userDtoMapper = $userDtoMapper;
    }


    /**
     * Retrieves a user from the external provider user data
     *
     * @param array $data The external provider user data: must have 'externalId', 'email', 'firstName' and 'lastName'
     *
     * @return User
     * @throws ORMException
     */
    protected function convertUser(array $data) : User
    {
        $this->logger->debug("Getting a user from the external provider user data",
            array ("provider" => $this->getProviderName(), "data" => $data));

        $externalId = $data[ self::EXTERNAL_ID ];
        /** @var ExternalIdentity $providerId */
        $providerId = $this->providerIdRepository->findOneByProvider($this->getProviderName(), $externalId);

        // the external provider ID matches a user -> return the user
        if (!empty($providerId))
        {
            $user = $providerId->getUser();

            $this->logger->debug("User exists for the provider", array ("providerId" => $providerId, "user" => $user));

            return $user;
        }

        $email = $data[ self::EMAIL ];
        /** @var User $user */
        $user = $this->userRepository->findOneBy(array ("email" => $email));

        // no user from the email -> create the user
        if (empty($user))
        {
            $this->logger->debug("No user exists with the e-mail address [$email], creating a new user",
                array ("provider" => $this->getProviderName(), "data" => $data));

            $user = new User($email, null, $data[ self::FIRST_NAME ], $data[ self::LAST_NAME ]);
            $this->entityManager->persist($user);
        }

        $this->logger->debug("Creating a provider identity for the user",
            array ("user" => $user, "provider" => $this->getProviderName()));

        // persist the user external provider ID
        $providerId = new ExternalIdentity($user, $this->getProviderName(), $externalId);
        $this->entityManager->persist($providerId);

        $this->entityManager->flush();

        $this->logger->info("Provider identity created for the user",
            array ("providerId" => $providerId, "user" => $user));

        return $user;
    }


    /**
     * Handles a provider access token to authenticate a user
     *
     * @param string $accessToken The access token
     *
     * @return UserDto The authenticated user
     * @throws InvalidCredentialsException
     * @throws ORMException
     */
    abstract public function handleAccessToken(string $accessToken) : UserDto;


    /**
     * Get the provider name
     *
     * @return string The provider name
     */
    abstract public function getProviderName() : string;


    /**
     * Creates an OAuth2 client
     *
     * @param array $config The client configuration
     *
     * @return mixed
     */
    abstract protected function createClient(array $config);

}
