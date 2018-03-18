<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Form\Type\Filter\UserFilterType;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\FilterFactory;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use ColocMatching\CoreBundle\Service\VisitorInterface;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Response\ResponseFactory;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use ColocMatching\RestBundle\Event\RegistrationEvent;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

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

    /** @var FilterFactory */
    private $filterBuilder;

    /** @var ResponseFactory */
    private $responseBuilder;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var RouterInterface */
    private $router;

    /** @var VisitorInterface */
    private $visitVisitor;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        UserDtoManagerInterface $userManager, FilterFactory $filterBuilder, ResponseFactory $responseBuilder,
        EventDispatcherInterface $eventDispatcher, RouterInterface $router, VisitorInterface $visitVisitor)
    {
        parent::__construct($logger, $serializer);
        $this->userManager = $userManager;
        $this->filterBuilder = $filterBuilder;
        $this->responseBuilder = $responseBuilder;
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
        $this->visitVisitor = $visitVisitor;
    }


    /**
     * Lists users or fields with pagination
     *
     * @Rest\Get(name="rest_get_users")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="20")
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results",
     *   default="createdAt")
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$",
     *   default="asc")
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getUsersAction(ParamFetcher $paramFetcher)
    {
        $pageable = $this->extractPageableParameters($paramFetcher);

        $this->logger->info("Listing users", $pageable);

        /** @var PageableFilter */
        $filter = $this->filterBuilder->createPageableFilter($pageable["page"], $pageable["size"], $pageable["order"],
            $pageable["sort"]);
        /** @var UserDto[] $users */
        $users = $this->userManager->list($filter);
        /** @var PageResponse */
        $response = $this->responseBuilder->createPageResponse($users, $this->userManager->countAll(), $filter);

        $this->logger->info("Listing users - result information", array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Creates a new User
     *
     * @Rest\Post(name="rest_create_user")
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
     * Gets a user or its fields by id
     *
     * @Rest\Get("/{id}", name="rest_get_user", requirements={"id"="\d+"})
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

        return new JsonResponse("User deleted", Response::HTTP_OK);
    }


    /**
     * Searches users by criteria
     *
     * @Rest\Post("/searches", name="rest_search_users")
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function searchUsersAction(Request $request)
    {
        $this->logger->info("Searching users by filtering", array ("request" => $request));

        /** @var UserFilter $filter */
        $filter = $this->filterBuilder->buildCriteriaFilter(UserFilterType::class, new UserFilter(),
            $request->request->all());
        /** @var UserDto[] $users */
        $users = $this->userManager->search($filter);
        /** @var PageResponse $response */
        $response = $this->responseBuilder->createPageResponse($users, $this->userManager->countBy($filter), $filter);

        $this->logger->info("Searching users by filtering - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Updates the status of an existing user
     *
     * @Rest\Patch("/{id}/status", name="rest_patch_user_status", requirements={"id"="\d+"})
     * @Security(expression="has_role('ROLE_API')")
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

        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        $user = $this->userManager->updateStatus($user, $request->request->getAlpha("value"));

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
