<?php

namespace App\Core\Security\User;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\User;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\User\UserRepository;
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
        $this->logger->debug("Encoding an authentication token for [{user}]", array ("user" => $user));

        /** @var User $userEntity */
        $userEntity = $this->userRepository->find($user->getId());
        $token = $this->tokenManager->create($userEntity);

        $this->logger->info("Token created for the user [{user}]", array ("user" => $userEntity));

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

        $user = $this->userManager->findByUsername($payload[ $property ]);

        $this->logger->info("Authenticated user found [{user}]", array ("user" => $user));

        return $user;
    }

}