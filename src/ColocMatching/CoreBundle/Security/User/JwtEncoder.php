<?php

namespace ColocMatching\CoreBundle\Security\User;

use ColocMatching\CoreBundle\DAO\UserDao;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
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

    /** @var UserDao */
    private $userDao;

    /** @var JWTTokenManagerInterface */
    private $tokenManager;

    /** @var TokenExtractorInterface */
    private $tokenExtractor;


    public function __construct(LoggerInterface $logger, UserDtoManagerInterface $userManager,
        UserDao $userDao, JWTTokenManagerInterface $tokenManager,
        TokenExtractorInterface $tokenExtractor)
    {
        $this->logger = $logger;
        $this->userManager = $userManager;
        $this->userDao = $userDao;
        $this->tokenManager = $tokenManager;
        $this->tokenExtractor = $tokenExtractor;
    }


    /**
     * @inheritdoc
     */
    public function encode(UserDto $user) : string
    {
        $this->logger->debug("Encoding an authentication token for a user", array ("user" => $user));

        try
        {
            /** @var User $userEntity */
            $userEntity = $this->userDao->read($user->getId());
            $token = $this->tokenManager->create($userEntity);

            return $token;
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->error("Unexpected error while encoding a JWT token for a user",
                array ("user" => $user, "exception" => $e));

            throw new \InvalidArgumentException("Unable to encode a JWT token", 0, $e);
        }
    }


    /**
     * @inheritdoc
     */
    public function decode(Request $request)
    {
        $this->logger->debug("Getting the authenticated user from the request", array ("request" => $request->headers));

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