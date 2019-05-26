<?php

namespace App\Rest\Controller\v1\User;

use App\Core\DTO\Page;
use App\Core\DTO\User\UserDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\Converter\StringConverterInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Repository\Filter\UserFilter;
use App\Rest\Controller\Response\Announcement\AnnouncementPageResponse;
use App\Rest\Controller\Response\Group\GroupPageResponse;
use App\Rest\Controller\Response\PageResponse;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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

    /** @var GroupDtoManagerInterface */
    private $groupManager;

    /** @var EventDispatcherVisitor */
    private $visitVisitor;

    /** @var StringConverterInterface */
    private $stringConverter;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, UserDtoManagerInterface $userManager,
        AnnouncementDtoManagerInterface $announcementManager, GroupDtoManagerInterface $groupManager,
        EventDispatcherVisitor $visitVisitor, StringConverterInterface $stringConverter)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->userManager = $userManager;
        $this->announcementManager = $announcementManager;
        $this->groupManager = $groupManager;
        $this->visitVisitor = $visitVisitor;
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
     * @Rest\QueryParam(
     *   name="q", nullable=true,
     *   description="Search query to filter results (csv), parameters are in the form 'name:value'")
     *
     * @Operation(tags={ "User" },
     *   @SWG\Response(response=200, description="Users found", @Model(type=UserPageResponse::class)),
     *   @SWG\Response(response=400, description="Invalid search query filter"),
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
        $filter = $this->extractQueryFilter(UserFilter::class, $paramFetcher, $this->stringConverter);
        $pageable = PageRequest::create($parameters);

        $this->logger->debug("Listing users", array_merge($parameters, ["filter" => $filter]));

        $result = empty($filter) ? $this->userManager->list($pageable) : $this->userManager->search($filter, $pageable);
        $response = new PageResponse($result, "rest_get_users", $paramFetcher->all());

        $this->logger->info("Listing users - result information", array ("response" => $response));

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


    /**
     * Lists a user's groups
     *
     * @Rest\Get(path="/{id}/groups", name="rest_get_user_groups", requirements={"id"="\d+"})
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters (prefix with '-' to DESC sort)",
     *   default="-createdAt")
     *
     * @Operation(tags={ "User" },
     *   @SWG\Response(response=200, description="Groups found", @Model(type=GroupPageResponse::class)),
     *   @SWG\Response(response=401, description="Unauthorized")
     * )
     * @Security(expression="is_granted('ROLE_USER')")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getGroupsAction(int $id, ParamFetcher $paramFetcher)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->debug("Listing the authenticated user's groups", $parameters);

        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        $pageable = PageRequest::create($parameters);

        $groups = $user->hasGroups() ?
            $this->groupManager->listByCreator($user, $pageable)
            : new Page($pageable, [], 0);

        $response = new PageResponse($groups, "rest_get_user_groups", array_merge(["id" => $id], $paramFetcher->all()));

        $this->logger->info("Listing the authenticated user's groups - result information",
            array ("response" => $response));

        return $this->buildJsonResponse($response);
    }

}
