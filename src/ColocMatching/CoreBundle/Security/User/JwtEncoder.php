<?php

namespace ColocMatching\CoreBundle\Security\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class JwtEncoder implements TokenEncoderInterface
{
    /** @var LoggerInterface */
    private $logger;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserRepository */
    private $userRepository;

    /** @var JWTTokenManagerInterface */
    private $tokenManager;

    /** @var TokenExtractorInterface */
    private $tokenExtractor;


    public function __construct(LoggerInterface $logger, UserDtoManagerInterface $userManager,
        EntityManagerInterface $entityManager, JWTTokenManagerInterface $tokenManager,
        TokenExtractorInterface $tokenExtractor)
    {
        $this->logger = $logger;
        $this->userManager = $userManager;
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->tokenManager = $tokenManager;
        $this->tokenExtractor = $tokenExtractor;
    }


    /**
     * @inheritdoc
     */
    public function encode(UserDto $user) : string
    {
        $this->logger->debug("Encoding an authentication token for a user", array ("user" => $user));

        /** @var User $userEntity */
        $userEntity = $this->userRepository->find($user->getId());
        $token = $this->tokenManager->create($userEntity);

        return $token;
    }


    /**
     * @inheritdoc
     */
    public function decode(Request $request)
    {
        $this->logger->debug("Getting the authenticated user from the request", array ("request" => $request));

        /** @var string $rawToken */
        $rawToken = $this->tokenExtractor->extract($request);

        if (empty($rawToken))
        {
            return null;
        }

        /** @var array $payload */
        $payload = $this->tokenManager->decode(new PreAuthenticationJWTUserToken($rawToken));

        if (empty($payload))
        {
            return null;
        }

        /** @var string $property */
        $property = $this->tokenManager->getUserIdentityField();

        return $this->userManager->findByUsername($payload[ $property ]);
    }

}