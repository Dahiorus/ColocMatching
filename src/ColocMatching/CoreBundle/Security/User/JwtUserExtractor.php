<?php

namespace ColocMatching\CoreBundle\Security\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class JwtUserExtractor
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TokenEncoderInterface
     */
    private $tokenEncoder;


    public function __construct(RequestStack $requestStack, TokenEncoderInterface $tokenEncoder)
    {
        $this->requestStack = $requestStack;
        $this->tokenEncoder = $tokenEncoder;
    }


    /**
     * Extracts the User from the authentication token in the request
     *
     * @param Request $request The request from which extract the user
     *
     * @return UserDto|null
     * @throws EntityNotFoundException
     */
    public function getAuthenticatedUser(Request $request = null)
    {
        if (empty($request))
        {
            $request = $this->requestStack->getCurrentRequest();
        }

        return $this->tokenEncoder->decode($request);
    }

}
