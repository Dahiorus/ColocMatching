<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\InvalidCredentialsException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Exception\AuthenticationException;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST Controller for authenticating User in the API
 *
 * @Rest\Route(path="/auth-tokens/", service="coloc_matching.rest.authentication_controller")
 *
 * @author Dahiorus
 */
class AuthenticationController extends AbstractRestController
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var JWTEncoderInterface */
    private $tokenEncoder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        UserDtoManagerInterface $userManager, JWTEncoderInterface $tokenEncoder)
    {
        parent::__construct($logger, $serializer);
        $this->userManager = $userManager;
        $this->tokenEncoder = $tokenEncoder;
    }


    /**
     * @Rest\Post("", name="rest_create_authtoken")
     * @Rest\RequestParam(name="_username", requirements="string", description="User login", nullable=false)
     * @Rest\RequestParam(name="_password", requirements="string", description="User password", nullable=false)
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws InvalidFormException
     * @throws JWTEncodeFailureException
     */
    public function postAuthTokenAction(Request $request)
    {
        /** @var string */
        $_username = $request->request->get("_username");
        $_password = $request->request->get("_password");

        $this->logger->info("Requesting an authentication token", array ("_username" => $_username));

        try
        {
            /** @var UserDto $user */
            $user = $this->userManager->checkUserCredentials($_username, $_password);

            $this->logger->debug("User found", array ("user" => $user));

            $token = $this->tokenEncoder->encode(array ("username" => $user->getEmail()));

            return new JsonResponse(
                array (
                    "token" => $token,
                    "user" => array (
                        "id" => $user->getId(),
                        "username" => $user->getUsername(),
                        "name" => $user->getDisplayName(),
                        "type" => $user->getType())), Response::HTTP_OK);
        }
        catch (InvalidCredentialsException $e)
        {
            throw new AuthenticationException();
        }
    }

}
