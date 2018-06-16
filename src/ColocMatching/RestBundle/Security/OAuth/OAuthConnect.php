<?php

namespace ColocMatching\RestBundle\Security\OAuth;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;
use ColocMatching\CoreBundle\Entity\User\ProviderIdentity;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidCredentialsException;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Repository\User\ProviderIdentityRepository;
use ColocMatching\CoreBundle\Repository\User\UserRepository;
use ColocMatching\RestBundle\Exception\OAuthConfigurationError;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

abstract class OAuthConnect
{
    protected const EXTERNAL_ID = "externalId";
    protected const EMAIL = "email";
    protected const FIRST_NAME = "firstName";
    protected const LAST_NAME = "lastName";
    protected const PICTURE = "picture";

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
     * @var ProviderIdentityRepository
     */
    protected $providerIdRepository;

    /**
     * @var UserDtoMapper
     */
    protected $userDtoMapper;

    /**
     * @var string
     */
    private $uploadDirectoryPath;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager,
        UserDtoMapper $userDtoMapper, string $uploadDirectoryPath)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->providerIdRepository = $entityManager->getRepository(ProviderIdentity::class);
        $this->userDtoMapper = $userDtoMapper;
        $this->uploadDirectoryPath = $uploadDirectoryPath;
    }


    /**
     * Retrieves a user from the external provider user data
     *
     * @param array $data The external provider user data: must have 'externalId', 'email', 'firstName' and 'lastName'
     *
     * @return User
     * @throws ORMException
     * @throws InvalidCredentialsException
     */
    protected function convertUser(array $data) : User
    {
        $this->logger->debug("Getting a user from the external provider user data",
            array ("provider" => $this->getProviderName(), "data" => $data));

        $externalId = $data[ self::EXTERNAL_ID ];
        /** @var ProviderIdentity $providerId */
        $providerId = $this->providerIdRepository->findOneByProvider($this->getProviderName(), $externalId);

        // the external provider ID matches a user -> return the user
        if (!empty($providerId))
        {
            $user = $providerId->getUser();

            $this->logger->debug("User exists for the provider", array ("providerId" => $providerId, "user" => $user));

            return $user;
        }

        $this->checkData($data); // check all data are valid

        $email = $data[ self::EMAIL ];
        /** @var User $user */
        $user = $this->userRepository->findOneBy(array ("email" => $email));

        // no user from the email -> create the user
        if (empty($user))
        {
            $this->logger->debug("No user exists with the e-mail address [$email], creating a new user",
                array ("provider" => $this->getProviderName(), "data" => $data));

            $user = $this->createUser($data);
        }

        $this->logger->debug("Creating a provider identity for the user",
            array ("user" => $user, "provider" => $this->getProviderName()));

        // persist the user external provider ID
        $providerId = new ProviderIdentity($user, $this->getProviderName(), $externalId);
        $this->entityManager->persist($providerId);

        $this->entityManager->flush();

        $this->logger->info("Provider identity created for the user",
            array ("providerId" => $providerId, "user" => $user));

        return $user;
    }


    /**
     * Checks all required data are provided by the provider
     *
     * @param array $data The data to check
     *
     * @throws InvalidCredentialsException
     */
    private function checkData(array $data)
    {
        $missingData = array ();

        if (empty($data[ self::EMAIL ]))
        {
            $missingData[] = self::EMAIL;
        }

        if (empty($data[ self::FIRST_NAME ]))
        {
            $missingData[] = self::FIRST_NAME;
        }

        if (empty($data[ self::LAST_NAME ]))
        {
            $missingData[] = self::LAST_NAME;
        }

        if (!empty($missingData))
        {
            throw new InvalidCredentialsException(
                sprintf("Invalid data sent by the provider '%s': [%s] are missing", $this->getProviderName(),
                    implode(", ", $missingData)));
        }
    }


    /**
     * Creates a user from the provider API data
     *
     * @param array $data The provider API data
     *
     * @return User The user with the provider API data
     */
    private function createUser(array $data) : User
    {
        $user = new User($data[ self::EMAIL ], null, $data[ self::FIRST_NAME ], $data[ self::LAST_NAME ]);
        $picture = $this->createProfilePicture($data);

        if (!empty($picture))
        {
            $this->logger->debug("Setting the user profile picture", array ("picture" => $picture));

            $user->setPicture($picture);
        }

        $this->entityManager->persist($user);

        return $user;
    }


    /**
     * Creates a profile picture to set in the user from the provider API data, if no picture is set in the data then
     * returns null
     *
     * @param array $data The provider API data
     *
     * @return null|ProfilePicture
     */
    private function createProfilePicture(array $data)
    {
        $pictureUrl = $data[ self::PICTURE ];

        if (empty($pictureUrl))
        {
            $this->logger->debug("No profile picture set for the provider user",
                array ("provider" => $this->getProviderName(), "data" => $data));

            return null;
        }

        $this->logger->debug("Uploading the user profile picture",
            array ("provider" => $this->getProviderName(), "pictureUrl" => $pictureUrl));

        // create a ProfilePicture with a file name
        $picture = new ProfilePicture();
        $name = sha1(uniqid($data[ self::EXTERNAL_ID ], true));
        $picture->setName(sprintf("%s.jpg", $name));

        // uploading the picture in the users picture folder
        $fullPath = sprintf(
            "%s/%s/%s", realpath($this->uploadDirectoryPath), $picture->getUploadDir(), $picture->getName());
        file_put_contents($fullPath, fopen($pictureUrl, "r"));

        return $picture;
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
     * @return mixed A OAuth2 API client
     * @throws OAuthConfigurationError
     */
    abstract public function createClient(array $config);

}
