<?php

namespace ColocMatching\CoreBundle\Security\User;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class JwtUserExtractor {

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
     * @var UserManagerInterface
     */
    private $userManager;


    public function __construct(TokenExtractorInterface $tokenExtractor,
        JWTEncoderInterface $jwtEncoder, RequestStack $requestStack, UserManagerInterface $userManager) {

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
     * @return User
     * @throws JWTDecodeFailureException
     * @throws UserNotFoundException
     */
    public function getAuthenticatedUser(Request $request = null) {
        if (empty($request)) {
            $request = $this->requestStack->getCurrentRequest();
        }

        /** @var string */
        $token = $this->tokenExtractor->extract($request);

        if (!empty($token)) {
            /** @var array */
            $payload = $this->jwtEncoder->decode($token);

            return $this->userManager->findByUsername($payload["username"]);
        }

        return null;
    }

}
