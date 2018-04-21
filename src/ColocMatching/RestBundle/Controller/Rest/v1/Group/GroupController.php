<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Group;

use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidCreatorException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidInviteeException;
use ColocMatching\CoreBundle\Form\Type\Filter\GroupFilterType;
use ColocMatching\CoreBundle\Manager\Group\GroupDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageRequest;
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
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * Lists groups or fields with pagination
     *
     * @Rest\Get(name="rest_get_groups")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", map=true, nullable=true, requirements="\w+,(asc|desc)", default="createdAt,asc",
     *   allowBlank=false, description="Sorting parameters")
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
     * Gets an existing group or its fields
     *
     * @Rest\Get("/{id}", name="rest_get_group")
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
     * Updates an existing group
     *
     * @Rest\Put("/{id}", name="rest_update_group")
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
     * Deletes an existing group
     *
     * @Rest\Delete("/{id}", name="rest_delete_group")
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
     * Patches an existing group
     *
     * @Rest\Patch("/{id}", name="rest_patch_group")
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
     * Searches groups by criteria
     *
     * @Rest\Post("/searches", name="rest_search_groups")
     * @Rest\RequestParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\RequestParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\RequestParam(name="sorts", map=true, nullable=true, requirements="\w+,(asc|desc)", default="createdAt,asc",
     *   allowBlank=false, description="Sorting parameters")
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

        $filter = $this->formValidator->validateFilterForm(GroupFilterType::class, new GroupFilter(),
            $request->request->all());
        $pageable = PageRequest::create($parameters);
        $response = new CollectionResponse($this->groupManager->search($filter, $pageable), "rest_search_groups");

        $this->logger->info("Searching groups by filter - result information", array ("response" => $response));

        return $this->buildJsonResponse($response);
    }


    /**
     * Gets all members of an existing group
     *
     * @Rest\Get("/{id}/members", name="rest_get_group_members")
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
     * Removes a member from an existing group
     *
     * @Rest\Delete("/{id}/members/{userId}", name="rest_remove_group_member")
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
