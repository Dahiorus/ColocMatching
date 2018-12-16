<?php

namespace App\Core\Security\User;

use App\Core\DTO\User\UserDto;
use App\Core\Manager\User\UserDtoManagerInterface;
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

    /** @var JWTTokenManagerInterface */
    private $tokenManager;

    /** @var TokenExtractorInterface */
    private $tokenExtractor;


    public function __construct(LoggerInterface $logger, UserDtoManagerInterface $userManager,
        JWTTokenManagerInterface $tokenManager, TokenExtractorInterface $tokenExtractor)
    {
        $this->logger = $logger;
        $this->userManager = $userManager;
        $this->tokenManager = $tokenManager;
        $this->tokenExtractor = $tokenExtractor;
    }


    /**
     * @inheritdoc
     */
    public function encode(UserDto $user) : string
    {
        $this->logger->debug("Encoding an authentication token for [{user}]", array ("user" => $user));

        $token = $this->tokenManager->create($user);

        $this->logger->info("JWT token created for the user [{user}]", array ("user" => $user));

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
        /** @var UserDto $user */
        $user = $this->userManager->findByUsername($payload[ $property ]);

        $this->logger->info("Authenticated user found [{user}]", array ("user" => $user));

        return $user;
    }

}
