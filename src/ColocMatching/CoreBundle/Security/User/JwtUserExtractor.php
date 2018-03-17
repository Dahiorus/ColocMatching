<?php

namespace ColocMatching\CoreBundle\Security\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class JwtUserExtractor
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TokenExtractorInterface
     */
    private $tokenExtractor;

    /**
     * @var JWTEncoderInterface
     */
    private $jwtEncoder;

    /**
     * @var UserDtoManagerInterface
     */
    private $userManager;


    public function __construct(TokenExtractorInterface $tokenExtractor,
        JWTEncoderInterface $jwtEncoder, RequestStack $requestStack, UserDtoManagerInterface $userManager)
    {

        $this->tokenExtractor = $tokenExtractor;
        $this->jwtEncoder = $jwtEncoder;
        $this->requestStack = $requestStack;
        $this->userManager = $userManager;
    }


    /**
     * Extracts the User from the authentication token in the request
     *
     * @param Request $request The request from which extract the user
     *
     * @return UserDto|null
     * @throws JWTDecodeFailureException
     * @throws EntityNotFoundException
     */
    public function getAuthenticatedUser(Request $request = null)
    {
        if (empty($request))
        {
            $request = $this->requestStack->getCurrentRequest();
        }

        /** @var string */
        $token = $this->tokenExtractor->extract($request);

        if (empty($token))
        {
            return null;
        }

        /** @var array */
        $payload = $this->jwtEncoder->decode($token);

        return $this->userManager->findByUsername($payload["username"]);
    }

}
