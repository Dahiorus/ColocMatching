<?php

namespace App\Rest\Controller\v1\Group;

use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Group\Group;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidInviteeException;
use App\Core\Form\Type\Group\GroupDtoForm;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Repository\Filter\Converter\StringConverterInterface;
use App\Core\Repository\Filter\GroupFilter;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Security\User\TokenEncoderInterface;
use App\Rest\Controller\Response\Group\GroupPageResponse;
use App\Rest\Controller\Response\PageResponse;
use App\Rest\Controller\v1\AbstractRestController;
use App\Rest\Listener\EventDispatcherVisitor;
use App\Rest\Security\Authorization\Voter\GroupVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST Controller for the resource /groups
 *
 * @Rest\Route(path="/groups")
 * @Security("is_granted('ROLE_USER')")
 *
 * @author Dahiorus
 */
class GroupController extends AbstractRestController
{
    /** @var GroupDtoManagerInterface */
    private $groupManager;

    /** @var RouterInterface */
    private $router;

    /** @var EventDispatcherVisitor */
    private $visitVisitor;

    /** @var TokenEncoderInterface */
    private $tokenEncoder;

    /** @var StringConverterInterface */
    private $stringConverter;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, GroupDtoManagerInterface $groupManager,
        RouterInterface $router, EventDispatcherVisitor $visitVisitor, TokenEncoderInterface $tokenEncoder,
        StringConverterInterface $stringConverter)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->groupManager = $groupManager;
        $this->router = $router;
        $this->visitVisitor = $visitVisitor;
        $this->tokenEncoder = $tokenEncoder;
        $this->stringConverter = $stringConverter;
    }


    /**
     * Lists groups
     *
     * @Rest\Get(name="rest_get_groups")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters (prefix with '-' to DESC sort)",
     *   default="-createdAt")
     * @Rest\QueryParam(
     *   name="q", nullable=true,
     *   description="Search query to filter results (csv), parameters are in the form 'name:value'")
     *
     * @Operation(tags={ "Group" },
     *   @SWG\Response(response=200, description="Groups found", @Model(type=GroupPageResponse::class)),
     *   @SWG\Response(response=400, description="Invalid search query filter"),
     *   @SWG\Response(response=401, description="Unauthorized")
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getGroupsAction(ParamFetcher $paramFetcher)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);
        $filter = $this->extractQueryFilter(GroupFilter::class, $paramFetcher, $this->stringConverter);
        $pageable = PageRequest::create($parameters);

        $this->logger->debug("Listing groups", array_merge($parameters, ["filter" => $filter]));

        $result = empty($filter) ? $this->groupManager->list($pageable)
            : $this->groupManager->search($filter, $pageable);
        $response = new PageResponse($result, "rest_get_groups", $paramFetcher->all());

        $this->logger->info("Listing groups - result information", array ("response" => $response));

        return $this->buildJsonResponse($response);
    }


    /**
     * Create a new group for the authenticated user
     *
     * @Rest\Post(name="rest_create_group")
     * @Security("is_granted('ROLE_SEARCH')")
     *
     * @Operation(tags={ "Group" },
     *   @SWG\Parameter(name="group", in="body", required=true, description="The group to create",
     *     @Model(type=GroupDtoForm::class)),
     *   @SWG\Response(response=201, description="Group created", @Model(type=GroupDto::class)),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function createGroupAction(Request $request)
    {
        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);

        $this->logger->debug("Posting a new group", array ("user" => $user, "postParams" => $request->request->all()));

        /** @var Group $group */
        $group = $this->groupManager->create($user, $request->request->all());

        $this->logger->info("Group created", array ("response" => $group));

        return $this->buildJsonResponse(
            $group, Response::HTTP_CREATED,
            array ("Location" => $this->router->generate("rest_get_group", array ("id" => $group->getId()),
                Router::ABSOLUTE_PATH)));
    }


    /**
     * Gets a group
     *
     * @Rest\Get("/{id}", name="rest_get_group")
     *
     * @Operation(tags={ "Group" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Response(response=200, description="Group found", @Model(type=GroupDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=404, description="No group found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function getGroupAction(int $id)
    {
        $this->logger->debug("Getting an existing group", array ("id" => $id));

        /** @var GroupDto $group */
        $group = $this->groupManager->read($id);

        $this->logger->info("One group found", array ("id" => $id, "response" => $group));

        $this->visitVisitor->visit($group);

        return $this->buildJsonResponse($group, Response::HTTP_OK);
    }


    /**
     * Updates a group
     *
     * @Rest\Put(path="/{id}", name="rest_update_group")
     * @Rest\Patch(path="/{id}", name="rest_patch_group")
     *
     * @Operation(tags={ "Group" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Parameter(name="group", in="body", required=true, description="The group to update",
     *     @Model(type=GroupDtoForm::class)),
     *   @SWG\Response(response=200, description="Group updated", @Model(type=GroupDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function updateGroupAction(int $id, Request $request)
    {
        $this->logger->debug("Updating an existing group", array ("id" => $id, "params" => $request->request->all()));

        /** @var GroupDto $group */
        $group = $this->groupManager->read($id);
        $this->evaluateUserAccess(GroupVoter::UPDATE, $group);

        $group = $this->groupManager->update($group, $request->request->all(), $request->isMethod("PUT"));

        $this->logger->info("Group updated", array ("response" => $group));

        return $this->buildJsonResponse($group, Response::HTTP_OK);
    }


    /**
     * Deletes a group
     *
     * @Rest\Delete("/{id}", name="rest_delete_group")
     *
     * @Operation(tags={ "Group" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Response(response=204, description="Group deleted"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function deleteGroupAction(int $id)
    {
        $this->logger->debug("Deleting an existing group", array ("id" => $id));

        try
        {
            /** @var GroupDto $group */
            $group = $this->groupManager->read($id);
            $this->evaluateUserAccess(GroupVoter::DELETE, $group);
            $this->groupManager->delete($group);
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->warning("Trying to delete an non existing group", array ("id" => $id));
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    /**
     * Gets all members of a group
     *
     * @Rest\Get("/{id}/members", name="rest_get_group_members")
     *
     * @Operation(tags={ "Group" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Response(
     *     response=200, description="Group members found",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=UserDto::class))) ),
     *   @SWG\Response(response=401, description="Unauthorized")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getMembersAction(int $id)
    {
        $this->logger->debug("Getting all members of an existing group", array ("id" => $id));

        /** @var GroupDto $group */
        $group = $this->groupManager->read($id);
        /** @var UserDto[] $members */
        $members = $this->groupManager->getMembers($group);

        $this->logger->info("Group members found", array ("members" => $members));

        return $this->buildJsonResponse($members, Response::HTTP_OK);
    }


    /**
     * Removes a member from a group
     *
     * @Rest\Delete("/{id}/members/{userId}", name="rest_remove_group_member")
     *
     * @Operation(tags={ "Group" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Parameter(in="path", name="userId", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(response=204, description="Group member deleted"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No group found")
     * )
     *
     * @param int $id
     * @param int $userId
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidInviteeException
     * @throws ORMException
     */
    public function removeMemberAction(int $id, int $userId)
    {
        $this->logger->debug("Removing a member of an existing group", array ("id" => $id, "userId" => $userId));

        /** @var GroupDto $group */
        $group = $this->groupManager->read($id);
        $this->evaluateUserAccess(GroupVoter::REMOVE_MEMBER, ["group" => $group, "userId" => $userId]);

        $member = new UserDto();
        $member->setId($userId);

        try
        {
            $this->groupManager->removeMember($group, $member);

            $this->logger->info("Group member removed", array ("member" => $member));
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->warning("Trying to remove a non existing member from a group",
                array ("group" => $group, "exception" => $e));
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
