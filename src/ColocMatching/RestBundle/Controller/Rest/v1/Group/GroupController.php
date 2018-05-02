<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Group;

use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidCreatorException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidInviteeException;
use ColocMatching\CoreBundle\Form\Type\Filter\GroupFilterForm;
use ColocMatching\CoreBundle\Form\Type\Group\GroupDtoForm;
use ColocMatching\CoreBundle\Manager\Group\GroupDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\PageRequest;
use ColocMatching\CoreBundle\Security\User\TokenEncoderInterface;
use ColocMatching\CoreBundle\Service\VisitorInterface;
use ColocMatching\CoreBundle\Validator\FormValidator;
use ColocMatching\RestBundle\Controller\Response\CollectionResponse;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use ColocMatching\RestBundle\Security\Authorization\Voter\GroupVoter;
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
 * @Rest\Route(path="/groups", service="coloc_matching.rest.group_controller")
 * @Security("has_role('ROLE_USER')")
 *
 * @author Dahiorus
 */
class GroupController extends AbstractRestController
{
    /** @var GroupDtoManagerInterface */
    private $groupManager;

    /** @var FormValidator */
    private $formValidator;

    /** @var RouterInterface */
    private $router;

    /** @var VisitorInterface */
    private $visitVisitor;

    /** @var TokenEncoderInterface */
    private $tokenEncoder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, GroupDtoManagerInterface $groupManager,
        FormValidator $formValidator, RouterInterface $router, VisitorInterface $visitVisitor,
        TokenEncoderInterface $tokenEncoder)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->groupManager = $groupManager;
        $this->formValidator = $formValidator;
        $this->router = $router;
        $this->visitVisitor = $visitVisitor;
        $this->tokenEncoder = $tokenEncoder;
    }


    /**
     * Lists groups
     *
     * @Rest\Get(name="rest_get_groups")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", map=true, description="Sorting parameters", requirements="\w+,(asc|desc)",
     *   default={ "createdAt,asc" }, allowBlank=false)
     *
     * @Operation(tags={ "Group" },
     *   @SWG\Response(response=200, description="Groups found"),
     *   @SWG\Response(response=206, description="Partial content"),
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

        $this->logger->info("Listing groups", $parameters);

        $pageable = PageRequest::create($parameters);
        $response = new PageResponse(
            $this->groupManager->list($pageable),
            "rest_get_groups", $paramFetcher->all(),
            $pageable, $this->groupManager->countAll());

        $this->logger->info("Listing groups - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Create a new group for the authenticated user
     *
     * @Rest\Post(name="rest_create_group")
     * @Security("has_role('ROLE_SEARCH')")
     *
     * @Operation(tags={ "Group" },
     *   @SWG\Parameter(name="group", in="body", required=true, description="The group to create",
     *     @Model(type=GroupDtoForm::class)),
     *   @SWG\Response(response=201, description="Group created", @Model(type=GroupDto::class)),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     * @throws InvalidCreatorException
     */
    public function createGroupAction(Request $request)
    {
        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);

        $this->logger->info("Posting a new group", array ("user" => $user, "request" => $request->request));

        /** @var Group $group */
        $group = $this->groupManager->create($user, $request->request->all());

        $this->logger->info("Group created", array ("response" => $group));

        return $this->buildJsonResponse($group,
            Response::HTTP_CREATED,
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
        $this->logger->info("Getting an existing group", array ("id" => $id));

        /** @var GroupDto $group */
        $group = $this->groupManager->read($id);

        $this->logger->info("One group found", array ("id" => $id, "response" => $group));

        $this->visitVisitor->visit($group);

        return $this->buildJsonResponse($group, Response::HTTP_OK);
    }


    /**
     * Updates a group
     *
     * @Rest\Put("/{id}", name="rest_update_group")
     *
     * @Operation(tags={ "Group" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Parameter(name="group", in="body", required=true, description="The group to update",
     *     @Model(type=GroupDtoForm::class)),
     *   @SWG\Response(response=200, description="Group updated", @Model(type=GroupDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=422, description="Validation error")
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
        $this->logger->info("Updating an existing group", array ("id" => $id, "request" => $request->request));

        return $this->handleUpdateGroupRequest($id, $request, true);
    }


    /**
     * Deletes a group
     *
     * @Rest\Delete("/{id}", name="rest_delete_group")
     *
     * @Operation(tags={ "Group" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Response(response=200, description="Group deleted"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteGroupAction(int $id)
    {
        $this->logger->info("Deleting an existing group", array ("id" => $id));

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

        return new JsonResponse("Group deleted");
    }


    /**
     * Updates (partial) a group
     *
     * @Rest\Patch("/{id}", name="rest_patch_group")
     *
     * @Operation(tags={ "Group" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Parameter(name="group", in="body", required=true, description="The group to update",
     *     @Model(type=GroupDtoForm::class)),
     *   @SWG\Response(response=200, description="Group updated", @Model(type=GroupDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function patchGroupAction(int $id, Request $request)
    {
        $this->logger->info("Patching an existing group", array ("id" => $id, "request" => $request->request));

        return $this->handleUpdateGroupRequest($id, $request, false);
    }


    /**
     * Searches specific groups
     *
     * @Rest\Post("/searches", name="rest_search_groups")
     * @Rest\RequestParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\RequestParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\RequestParam(name="sorts", map=true, description="Sorting parameters", requirements="\w+,(asc|desc)",
     *   default={ "createdAt,asc" }, allowBlank=false)
     *
     * @Operation(tags={ "Group" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Parameter(name="group", in="body", required=true, description="The group to update",
     *     @Model(type=GroupFilterForm::class)),
     *   @SWG\Response(response=200, description="Groups found"),
     *   @SWG\Response(response=401, description="Unauthorized"),
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
    public function searchGroupsAction(ParamFetcher $paramFetcher, Request $request)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->info("Searching specific  groups",
            array_merge(array ("postParams" => $request->request->all()), $parameters));

        $filter = $this->formValidator->validateFilterForm(GroupFilterForm::class, new GroupFilter(),
            $request->request->all());
        $pageable = PageRequest::create($parameters);
        $response = new CollectionResponse($this->groupManager->search($filter, $pageable), "rest_search_groups");

        $this->logger->info("Searching groups by filter - result information", array ("response" => $response));

        return $this->buildJsonResponse($response);
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
        $this->logger->info("Getting all members of an existing group", array ("id" => $id));

        /** @var GroupDto $group */
        $group = $this->groupManager->read($id);

        return $this->buildJsonResponse($this->groupManager->getMembers($group), Response::HTTP_OK);
    }


    /**
     * Removes a member from a group
     *
     * @Rest\Delete("/{id}/members/{userId}", name="rest_remove_group_member")
     *
     * @Operation(tags={ "Group" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Parameter(in="path", name="userId", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(response=200, description="Group member deleted"),
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
        $this->logger->info("Removing a member of an existing group", array ("id" => $id, "userId" => $userId));

        /** @var GroupDto $group */
        $group = $this->groupManager->read($id);
        $this->evaluateUserAccess(GroupVoter::REMOVE_MEMBER, $group);

        $member = new UserDto();
        $member->setId($userId);

        $this->groupManager->removeMember($group, $member);

        return new JsonResponse("Member removed");
    }


    /**
     * Handles the update operation on the group
     *
     * @param int $id The group identifier
     * @param Request $request The request to handle
     * @param bool $fullUpdate If the operation is a patch or a full update
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    private function handleUpdateGroupRequest(int $id, Request $request, bool $fullUpdate)
    {
        /** @var GroupDto $group */
        $group = $this->groupManager->read($id);
        $this->evaluateUserAccess(GroupVoter::UPDATE, $group);
        $group = $this->groupManager->update($group, $request->request->all(), $fullUpdate);

        $this->logger->info("Group updated", array ("response" => $group));

        return $this->buildJsonResponse($group, Response::HTTP_OK);
    }
}
