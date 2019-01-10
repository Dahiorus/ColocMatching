<?php

namespace App\Rest\Controller\v1\User;

use App\Core\DTO\Page;
use App\Core\DTO\User\UserDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\UnsupportedSerializationException;
use App\Core\Form\Type\Filter\UserFilterForm;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\Converter\StringConverterInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Repository\Filter\UserFilter;
use App\Core\Validator\FormValidator;
use App\Rest\Controller\Response\Announcement\AnnouncementPageResponse;
use App\Rest\Controller\Response\CollectionResponse;
use App\Rest\Controller\Response\PageResponse;
use App\Rest\Controller\Response\User\UserCollectionResponse;
use App\Rest\Controller\Response\User\UserPageResponse;
use App\Rest\Controller\v1\AbstractRestController;
use App\Rest\Listener\EventDispatcherVisitor;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for resource /users
 *
 * @Rest\Route(path="/users")
 *
 * @author Dahiorus
 */
class UserController extends AbstractRestController
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var FormValidator */
    private $formValidator;

    /** @var EventDispatcherVisitor */
    private $visitVisitor;

    /** @var RouterInterface */
    private $router;

    /** @var StringConverterInterface */
    private $stringConverter;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, UserDtoManagerInterface $userManager,
        AnnouncementDtoManagerInterface $announcementManager, FormValidator $formValidator,
        EventDispatcherVisitor $visitVisitor, RouterInterface $router, StringConverterInterface $stringConverter)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->userManager = $userManager;
        $this->announcementManager = $announcementManager;
        $this->formValidator = $formValidator;
        $this->visitVisitor = $visitVisitor;
        $this->router = $router;
        $this->stringConverter = $stringConverter;
    }


    /**
     * Lists users
     *
     * @Rest\Get(name="rest_get_users")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters (prefix with '-' to DESC sort)",
     *   default="-createdAt")
     *
     * @Operation(tags={ "User" },
     *   @SWG\Response(response=200, description="Users found", @Model(type=UserPageResponse::class)),
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

        $this->logger->debug("Listing users", $parameters);

        $pageable = PageRequest::create($parameters);
        $response = new PageResponse($this->userManager->list($pageable), "rest_get_users", $paramFetcher->all());

        $this->logger->info("Listing users - result information",
            array ("pageable" => $pageable, "response" => $response));

        return $this->buildJsonResponse($response);
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
        $this->logger->debug("Getting an existing user", array ("id" => $id));

        /** @var UserDto $user */
        $user = $this->userManager->read($id);

        $this->logger->info("One user found", array ("response" => $user));

        $this->visitVisitor->visit($user);

        return $this->buildJsonResponse($user, Response::HTTP_OK);
    }


    /**
     * Searches specific users
     *
     * @Rest\Post(path="/searches", name="rest_search_users")
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(name="filter", in="body", required=true, description="Criteria filter",
     *     @Model(type=UserFilterForm::class)),
     *   @SWG\Response(response=201, description="Users found", @Model(type=UserCollectionResponse::class)),
     *   @SWG\Response(response=400, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function searchUsersAction(Request $request)
    {
        $this->logger->debug("Searching specific users", array ("postParams" => $request->request->all()));

        /** @var UserFilter $filter */
        $filter = $this->formValidator->validateFilterForm(UserFilterForm::class, new UserFilter(),
            $request->request->all());
        $convertedFilter = $this->stringConverter->toString($filter);

        $response = new CollectionResponse(
            $this->userManager->search($filter, $filter->getPageable()),
            "rest_get_searched_users", array ("filter" => $convertedFilter));

        $this->logger->info("Searching users by filtering - result information",
            array ("filter" => $filter, "response" => $response));

        $location = $this->router->generate("rest_get_searched_users", array ("filter" => $convertedFilter),
            Router::ABSOLUTE_URL);

        return $this->buildJsonResponse($response, Response::HTTP_CREATED, array ("Location" => $location));
    }


    /**
     * Gets searched users from the base 64 JSON string filter
     *
     * @Rest\Get(path="/searches/{filter}", name="rest_get_searched_users")
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(
     *     name="filter", in="path", type="string", required=true, description="Base 64 JSON string filter"),
     *   @SWG\Response(response=200, description="Users found", @Model(type=UserCollectionResponse::class)),
     *   @SWG\Response(response=404, description="Unsupported base64 string conversion")
     * )
     *
     * @param string $filter
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getSearchedUsersAction(string $filter)
    {
        $this->logger->debug("Getting searched users from a base 64 string filter", array ("filter" => $filter));

        try
        {
            /** @var UserFilter $userFilter */
            $userFilter = $this->stringConverter->toObject($filter, UserFilter::class);
        }
        catch (UnsupportedSerializationException $e)
        {
            throw new NotFoundHttpException("No filter found with the given base64 string", $e);
        }

        $response = new CollectionResponse(
            $this->userManager->search($userFilter, $userFilter->getPageable()),
            "rest_get_searched_users", array ("filter" => $filter));

        $this->logger->info("Searching users by filtering - result information",
            array ("filter" => $userFilter, "response" => $response));

        return $this->buildJsonResponse($response);
    }


    /**
     * Lists a user's announcements
     *
     * @Rest\Get(path="/{id}/announcements", name="rest_get_user_announcements", requirements={"id"="\d+"})
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters (prefix with '-' to DESC sort)",
     *   default="-createdAt")
     *
     * @Operation(tags={ "User" },
     *   @SWG\Response(response=200, description="Announcements found", @Model(type=AnnouncementPageResponse::class)),
     * )
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getAnnouncementsAction(int $id, ParamFetcher $paramFetcher)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->debug("Listing the authenticated user's announcements", $parameters);

        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        $pageable = PageRequest::create($parameters);

        $announcements = $user->hasAnnouncements() ?
            $this->announcementManager->listByCreator($user, $pageable)
            : new Page($pageable, [], 0);

        $response = new PageResponse(
            $announcements, "rest_get_user_announcements", array_merge(["id" => $id], $paramFetcher->all()));

        $this->logger->info("Listing the authenticated user's announcements - result information",
            array ("response" => $response));

        return $this->buildJsonResponse($response);
    }

}
