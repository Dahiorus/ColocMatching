<?php

namespace ColocMatching\RestBundle\Security\OAuth;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\ExternalIdentity;
use ColocMatching\CoreBundle\Exception\InvalidCredentialsException;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Repository\User\ExternalIdentityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

abstract class OAuthConnect
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ExternalIdentityRepository
     */
    protected $externalIdRepository;

    /**
     * @var UserDtoMapper
     */
    protected $userDtoMapper;

    /**
     * @var string[]
     */
    protected $scope;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager,
        UserDtoMapper $userDtoMapper, array $scope)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->externalIdRepository = $entityManager->getRepository(ExternalIdentity::class);
        $this->userDtoMapper = $userDtoMapper;
        $this->scope = $scope;
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
    public abstract function handleAccessToken(string $accessToken) : UserDto;


    /**
     * Get the provider name
     *
     * @return string The provider name
     */
    public abstract function getProviderName() : string;

}
