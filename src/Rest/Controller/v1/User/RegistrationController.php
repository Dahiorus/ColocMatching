<?php

namespace App\Rest\Controller\v1\User;

use App\Core\DTO\User\UserDto;
use App\Core\DTO\User\UserTokenDto;
use App\Core\Entity\User\UserStatus;
use App\Core\Entity\User\UserToken;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Form\Type\User\RegistrationForm;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Manager\User\UserTokenDtoManagerInterface;
use App\Rest\Controller\v1\AbstractRestController;
use App\Rest\Event\Events;
use App\Rest\Event\RegistrationConfirmedEvent;
use App\Rest\Event\RegistrationEvent;
use Doctrine\ORM\ORMException;
use Exception;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Rest\Route(path="/registrations")
 *
 * @author Dahiorus
 */
class RegistrationController extends AbstractRestController
{
    /** @var UserTokenDtoManagerInterface */
    private $userTokenManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var RouterInterface */
    private $router;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, UserTokenDtoManagerInterface $userTokenManager,
        UserDtoManagerInterface $userManager, RouterInterface $router, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->userTokenManager = $userTokenManager;
        $this->userManager = $userManager;
        $this->router = $router;
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * Registers a new user
     *
     * @Rest\Post(name="rest_register_user")
     *
     * @Operation(tags={ "Registration" },
     *   @SWG\Parameter(name="user", in="body", required=true, description="User to register",
     *     @Model(type=RegistrationForm::class)),
     *   @SWG\Response(response=201, description="User registered", @Model(type=UserDto::class)),
     *   @SWG\Response(response=403, description="User already authenticated"),
     *   @SWG\Response(response=400, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws InvalidParameterException
     */
    public function registerUserAction(Request $request)
    {
        $this->logger->debug("Registering a new user");

        /** @var UserDto $user */
        $user = $this->userManager->create($request->request->all());
        $this->eventDispatcher->dispatch(new RegistrationEvent($user), Events::USER_REGISTERED_EVENT);

        $this->logger->info("User registered", array ("response" => $user));

        return $this->buildJsonResponse($user, Response::HTTP_CREATED,
            array ("Location" => $this->router->generate("rest_get_user", array ("id" => $user->getId()),
                Router::ABSOLUTE_URL)));
    }


    /**
     * Confirms a user registration
     *
     * @Rest\Post(path="/confirmation", name="rest_confirm_registration")
     *
     * @Operation(tags={ "Registration" },
     *   @SWG\Parameter(
     *     name="token", required=true, in="body",
     *     @SWG\Schema(
     *       @SWG\Property(property="value", type="string", description="The token value"), required={ "value" })
     *   ),
     *   @SWG\Response(response=200, description="User registration confirmed", @Model(type=UserDto::class)),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=403, description="User already authenticated"),
     *   @SWG\Response(response=404, description="No user found")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidParameterException
     * @throws ORMException
     */
    public function confirmAction(Request $request)
    {
        $this->logger->debug("Confirming a user registration");

        $userToken = $this->getUserToken($request);

        try
        {
            $isExpired = $userToken->isExpired();
        }
        catch (Exception $e)
        {
            $this->logger->critical("Unable to tell if the user token [{token}] is expired",
                array ("token" => $userToken, "exception" => $e));

            throw new RuntimeException("Unexpected error on confirming a user registration", 500, $e);
        }

        if ($isExpired)
        {
            throw new BadRequestHttpException("The token [$userToken] is expired");
        }

        $user = $this->userManager->findByUsername($userToken->getUsername());
        $user = $this->userManager->updateStatus($user, UserStatus::ENABLED);

        $this->userTokenManager->delete($userToken);
        $this->eventDispatcher->dispatch(new RegistrationConfirmedEvent($user),
            Events::USER_REGISTRATION_CONFIRMED_EVENT);

        $this->logger->info("User registration confirmed", array ("user" => $user));

        return $this->buildJsonResponse($user);
    }


    /**
     * Gets a UserToken from the specified request
     *
     * @param Request $request The request
     *
     * @return UserTokenDto
     */
    private function getUserToken(Request $request) : UserTokenDto
    {
        $tokenValue = $request->request->get("value");

        if (empty($tokenValue))
        {
            throw new BadRequestHttpException("Empty token value");
        }

        try
        {
            $userToken = $this->userTokenManager->getByToken($tokenValue, UserToken::REGISTRATION_CONFIRMATION);
        }
        catch (EntityNotFoundException $e)
        {
            throw new BadRequestHttpException("Unknown user token", $e);
        }

        return $userToken;
    }

}
