<?php

namespace App\Rest\Controller\v1;

use App\Core\DTO\User\UserDto;
use App\Core\Exception\InvalidCredentialsException;
use App\Core\Exception\InvalidFormException;
use App\Core\Form\Type\Security\LoginForm;
use App\Core\Security\User\TokenEncoderInterface;
use App\Rest\Exception\AuthenticationException;
use App\Rest\Security\OAuth\OAuthConnect;
use App\Rest\Security\OAuth\OAuthConnectRegistry;
use App\Rest\Security\UserAuthenticationHandler;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST Controller for authenticating User in the API
 *
 * @Rest\Route(path="/auth/tokens")
 *
 * @author Dahiorus
 */
class AuthenticationController extends AbstractRestController
{
    /** @var UserAuthenticationHandler */
    private $authenticationHandler;

    /** @var OAuthConnectRegistry */
    private $oauthConnectRegistry;

    /** @var TokenEncoderInterface */
    private $tokenEncoder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, UserAuthenticationHandler $authenticationHandler,
        OAuthConnectRegistry $oauthConnectRegistry, TokenEncoderInterface $tokenEncoder)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->authenticationHandler = $authenticationHandler;
        $this->oauthConnectRegistry = $oauthConnectRegistry;
        $this->tokenEncoder = $tokenEncoder;
    }


    /**
     * Authenticates a user
     *
     * @Rest\Post(name="rest_authenticate_user")
     * @Operation(tags={ "Authentication" },
     *   @SWG\Parameter(name="credentials", in="body", @Model(type=LoginForm::class), required=true),
     *   @SWG\Response(
     *     response=201, description="User authenticated",
     *     @SWG\Schema(type="object",
     *       @SWG\Property(property="token", type="string", description="The authentication token"))
     *   ),
     *   @SWG\Response(response=401, description="Authentication error"),
     *   @SWG\Response(response=403, description="User already authenticated")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws InvalidFormException
     */
    public function authenticateUserAction(Request $request)
    {
        /** @var string */
        $_username = $request->request->get("_username");
        $_password = $request->request->get("_password");

        $this->logger->debug("Requesting an authentication token", array ("_username" => $_username));

        try
        {
            /** @var UserDto $user */
            $user = $this->authenticationHandler->handleCredentials($_username, $_password);
            $token = $this->tokenEncoder->encode($user);

            $this->logger->info("User authenticated", array ("user" => $user));

            return $this->buildResponse($token);
        }
        catch (InvalidCredentialsException $e)
        {
            throw new AuthenticationException();
        }
    }


    /**
     * Authenticates a user from an external identity provider
     *
     * @Rest\Post(path="/{provider}", name="rest_authenticate_oauth_user", requirements={ "provider"="\w+" })
     * @Operation(tags={ "Authentication" },
     *   @SWG\Parameter(
     *     in="path", name="provider", type="string", required=true, description="The external identity provider name"),
     *   @SWG\Parameter(name="credentials", in="body", required=true,
     *     @SWG\Schema(required={"accessToken"},
     *       @SWG\Property(property="accessToken", type="string", description="Provider access token") )),
     *   @SWG\Response(
     *     response=201, description="User authenticated",
     *     @SWG\Schema(type="object",
     *       @SWG\Property(property="token", type="string", description="The authentication token"))
     *   ),
     *   @SWG\Response(response=401, description="Authentication error"),
     *   @SWG\Response(response=403, description="User already authenticated")
     * )
     *
     * @param string $provider
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function authenticateOAuthUserAction(string $provider, Request $request)
    {
        $accessToken = $request->request->get("accessToken");

        $this->logger->debug("Requesting an authentication token for an external provider user",
            array ("provider" => $provider));

        try
        {
            /** @var OAuthConnect $oauthConnect */
            $oauthConnect = $this->oauthConnectRegistry->get($provider);
            /** @var UserDto $user */
            $user = $oauthConnect->handleAccessToken($accessToken);
            $token = $this->tokenEncoder->encode($user);

            $this->logger->info("User authenticated", array ("user" => $user));

            return $this->buildResponse($token);
        }
        catch (InvalidCredentialsException $e)
        {
            throw new AuthenticationException(
                sprintf("Authentication error on the provider '%s': %s", $provider, $e->getMessage()), $e);
        }
    }


    /**
     * Build a JsonResponse from the user information and the JWT token
     *
     * @param UserDto $user The user
     * @param string $token The JWT token authenticating the user
     *
     * @return JsonResponse
     */
    private function buildResponse(string $token) : JsonResponse
    {
        return $this->buildJsonResponse(array (
            "token" => $token,
        ), Response::HTTP_CREATED);
    }

}
