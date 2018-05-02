<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Form\Type\Filter\UserFilterForm;
use ColocMatching\CoreBundle\Form\Type\User\RegistrationForm;
use ColocMatching\CoreBundle\Form\Type\User\UserDtoForm;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\PageRequest;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use ColocMatching\CoreBundle\Service\VisitorInterface;
use ColocMatching\CoreBundle\Validator\FormValidator;
use ColocMatching\RestBundle\Controller\Response\CollectionResponse;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use ColocMatching\RestBundle\Event\RegistrationEvent;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for resource /users
 *
 * @Rest\Route(path="/users", service="coloc_matching.rest.user_controller")
 *
 * @author Dahiorus
 */
class UserController extends AbstractRestController
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var FormValidator */
    private $formValidator;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var RouterInterface */
    private $router;

    /** @var VisitorInterface */
    private $visitVisitor;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, UserDtoManagerInterface $userManager,
        FormValidator $formValidator, EventDispatcherInterface $eventDispatcher, RouterInterface $router,
        VisitorInterface $visitVisitor)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->userManager = $userManager;
        $this->formValidator = $formValidator;
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
        $this->visitVisitor = $visitVisitor;
    }


    /**
     * Lists users
     *
     * @Rest\Get(name="rest_get_users")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", map=true, description="Sorting parameters", requirements="\w+,(asc|desc)",
     *   default={ "createdAt,asc" }, allowBlank=false)
     *
     * @Operation(tags={ "User" },
     *   @SWG\Response(response=200, description="Users found"),
     *   @SWG\Response(response=206, description="Partial content")
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getUsersAction(ParamFetcher $paramFetcher)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->info("Listing users", $parameters);

        $pageable = PageRequest::create($parameters);
        $response = new PageResponse(
            $this->userManager->list($pageable),
            "rest_get_users", $paramFetcher->all(),
            $pageable, $this->userManager->countAll());

        $this->logger->info("Listing users - result information",
            array ("pageable" => $pageable, "response" => $response));

        return $this->buildJsonResponse($response, ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT :
            Response::HTTP_OK);
    }


    /**
     * Creates a new user
     *
     * @Rest\Post(name="rest_create_user")
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(name="user", in="body", required=true, description="User to register",
     *     @Model(type=RegistrationForm::class)),
     *   @SWG\Response(response=201, description="User registered", @Model(type=UserDto::class)),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     */
    public function createUserAction(Request $request)
    {
        $this->logger->info("Posting a new user", array ("postParams" => $request->request->all()));

        /** @var UserDto $user */
        $user = $this->userManager->create($request->request->all());
        $this->eventDispatcher->dispatch(RegistrationEvent::REGISTERED_EVENT, new RegistrationEvent($user));

        $this->logger->info("User created", array ("response" => $user));

        return $this->buildJsonResponse($user, Response::HTTP_CREATED,
            array ("Location" => $this->router->generate("rest_get_user", array ("id" => $user->getId()),
                Router::ABSOLUTE_URL)));
    }


    /**
     * Gets a user
     *
     * @Rest\Get("/{id}", name="rest_get_user", requirements={"id"="\d+"})
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(response=200, description="User found", @Model(type=UserDto::class)),
     *   @SWG\Response(response=404, description="No user found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function getUserAction(int $id)
    {
        $this->logger->info("Getting an existing user", array ("id" => $id));

        /** @var UserDto $user */
        $user = $this->userManager->read($id);

        $this->logger->info("One user found", array ("response" => $user));

        $this->visitVisitor->visit($user);

        return $this->buildJsonResponse($user, Response::HTTP_OK);
    }


    /**
     * Updates an existing user
     *
     * @Rest\Put("/{id}", name="rest_update_user", requirements={"id"="\d+"})
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(name="user", in="body", required=true, description="User to update",
     *     @Model(type=UserDtoForm::class)),
     *   @SWG\Response(response=200, description="User updated", @Model(type=UserDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=404, description="No user found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws EntityNotFoundException
     */
    public function updateUserAction(int $id, Request $request)
    {
        $this->logger->info("Putting an existing user", array ("id" => $id, "putParams" => $request->request->all()));

        return $this->handleUpdateUserRequest($id, $request, true);
    }


    /**
     * Updates (partial) an existing user
     *
     * @Rest\Patch("/{id}", name="rest_patch_user", requirements={"id"="\d+"})
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(name="user", in="body", required=true, description="User to update",
     *     @Model(type=UserDtoForm::class)),
     *   @SWG\Response(response=200, description="User updated", @Model(type=UserDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=404, description="No user found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws EntityNotFoundException
     */
    public function patchUserAction(int $id, Request $request)
    {
        $this->logger->info("Patching an existing user",
            array ("id" => $id, "patchParams" => $request->request->all()));

        return $this->handleUpdateUserRequest($id, $request, false);
    }


    /**
     * Deletes an existing user
     *
     * @Rest\Delete("/{id}", name="rest_delete_user", requirements={"id"="\d+"})
     * @Security(expression="has_role('ROLE_API')")
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(response=200, description="User deleted"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteUserAction(int $id)
    {
        $this->logger->info("Deleting an existing user", array ("id" => $id));

        try
        {
            /** @var UserDto $user */
            $user = $this->userManager->read($id);

            if (!empty($user))
            {
                $this->logger->info("User found", array ("user" => $user));

                $this->userManager->delete($user);
            }
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->warning("Trying to delete an non existing user", array ("id" => $id));
        }

        return new JsonResponse("User deleted");
    }


    /**
     * Searches specific users
     *
     * @Rest\Post("/searches", name="rest_search_users")
     * @Rest\RequestParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\RequestParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\RequestParam(name="sorts", map=true, description="Sorting parameters", requirements="\w+,(asc|desc)",
     *   default={ "createdAt,asc" }, allowBlank=false)
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(name="filter", in="body", required=true, description="Criteria filter",
     *     @Model(type=UserFilterForm::class)),
     *   @SWG\Response(response=200, description="Users found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function searchUsersAction(ParamFetcher $paramFetcher, Request $request)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->info("Searching specific users",
            array_merge(array ("postParams" => $request->request->all()), $parameters));

        $filter = $this->formValidator->validateFilterForm(UserFilterForm::class, new UserFilter(),
            $request->request->all());
        $pageable = PageRequest::create($parameters);
        $response = new CollectionResponse($this->userManager->search($filter, $pageable), "rest_search_users");

        $this->logger->info("Searching users by filtering - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response);
    }


    /**
     * Updates an existing user status
     *
     * @Rest\Patch("/{id}/status", name="rest_patch_user_status", requirements={"id"="\d+"})
     * @Security(expression="has_role('ROLE_API')")
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(
     *     name="status", required=true, in="body",
     *     @SWG\Schema(required={ "value" },
     *       @SWG\Property(property="value", type="string", description="The value of the status",
     *         enum={"enabled", "vacation", "banned"}, default="enabled"))),
     *   @SWG\Response(response=200, description="User status updated", @Model(type=UserDto::class)),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No user found")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidParameterException
     */
    public function updateStatusAction(int $id, Request $request)
    {
        $this->logger->info("Changing the status of a user",
            array ("id" => $id, "patchParams" => $request->request->all()));

        /** @var string $status */
        $status = $request->request->getAlpha("value");
        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        $user = $this->userManager->updateStatus($user, $status);

        $this->logger->info("User status updated", array ("response" => $user));

        return $this->buildJsonResponse($user, Response::HTTP_OK);
    }


    /**
     * Handles the update operation of the user
     *
     * @param int $id The user identifier
     * @param Request $request The current request
     * @param bool $fullUpdate If the operation is a patch or a full update
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    private function handleUpdateUserRequest(int $id, Request $request, bool $fullUpdate)
    {
        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        $user = $this->userManager->update($user, $request->request->all(), $fullUpdate);

        $this->logger->info("User updated", array ("response" => $user));

        return $this->buildJsonResponse($user, Response::HTTP_OK);
    }

}
