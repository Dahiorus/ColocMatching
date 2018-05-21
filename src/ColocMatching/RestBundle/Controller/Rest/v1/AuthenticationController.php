<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\InvalidCredentialsException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Security\LoginForm;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Security\User\TokenEncoderInterface;
use ColocMatching\RestBundle\Exception\AuthenticationException;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST Controller for authenticating User in the API
 *
 * @Rest\Route(path="/auth/tokens", service="coloc_matching.rest.authentication_controller")
 *
 * @author Dahiorus
 */
class AuthenticationController extends AbstractRestController
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var TokenEncoderInterface */
    private $tokenEncoder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, UserDtoManagerInterface $userManager,
        TokenEncoderInterface $tokenEncoder)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->userManager = $userManager;
        $this->tokenEncoder = $tokenEncoder;
    }


    /**
     * Authenticates a user
     *
     * @Rest\Post(name="rest_authenticate_user")
     *
     * @SWG\Post(tags={ "Authentication" },
     *   @SWG\Parameter(name="credentials", in="body", @Model(type=LoginForm::class), required=true),
     *   @SWG\Response(
     *     response=201, description="User authenticated",
     *     @SWG\Schema(type="object",
     *       @SWG\Property(property="token", type="string", description="The authentication token"),
     *       @SWG\Property(property="user", type="object", description="User information",
     *         @SWG\Property(property="id", type="integer", description="User's identifier", example="1"),
     *         @SWG\Property(property="username", type="string", description="User's username", example="username"),
     *         @SWG\Property(property="name", type="string", description="User's display name", example="User User"),
     *         @SWG\Property(property="type", type="string", description="User's type", example="search") ))
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
            $user = $this->userManager->findByCredentials($_username, $_password);
            $token = $this->tokenEncoder->encode($user);

            $this->logger->info("User authenticated", array ("user" => $user));

            return new JsonResponse(
                array (
                    "token" => $token,
                    "user" => array (
                        "id" => $user->getId(),
                        "username" => $user->getUsername(),
                        "name" => $user->getDisplayName(),
                        "type" => $user->getType())), Response::HTTP_CREATED);
        }
        catch (InvalidCredentialsException $e)
        {
            throw new AuthenticationException();
        }
    }

}
