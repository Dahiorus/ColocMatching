<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Group;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Exception\GroupNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidCreatorException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Filter\GroupFilterType;
use ColocMatching\CoreBundle\Manager\Group\GroupManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\Group\GroupControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST Controller for the resource /groups
 *
 * @Rest\Route("/groups")
 *
 * @author Dahiorus
 */
class GroupController extends RestController implements GroupControllerInterface {

    /**
     * Lists groups or fields with pagination
     *
     * @Rest\Get("", name="rest_get_groups")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="20")
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results",
     *   default="id")
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$",
     *   default="asc")
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return for each result")
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getGroupsAction(ParamFetcher $paramFetcher) {
        $pageable = $this->extractPageableParameters($paramFetcher);
        $fields = $paramFetcher->get("fields");

        $this->get("logger")->info("Listing groups", array ("pagination" => $pageable, "fields" => $fields));

        /** @var PageableFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($pageable["page"],
            $pageable["size"], $pageable["order"], $pageable["sort"]);
        /** @var GroupManagerInterface */
        $manager = $this->get("coloc_matching.core.group_manager");
        /** @var array */
        $groups = empty($fields) ? $manager->list($filter) : $manager->list($filter, explode(",", $fields));
        /** @var PageResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($groups,
            $manager->countAll(), $filter);

        $this->get("logger")->info("Listing groups - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Create a new group for the authenticated user
     *
     * @Rest\Post("", name="rest_create_group")
     *
     * @Security(expression="has_role('ROLE_SEARCH')")
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws InvalidCreatorException
     */
    public function createGroupAction(Request $request) {
        /** @var User */
        $user = $this->extractUser($request);

        $this->get("logger")->info("Posting a new group", array ("user" => $user, "request" => $request->request));

        /** @var Group */
        $group = $this->get("coloc_matching.core.group_manager")->create($user, $request->request->all());
        /** @var string */
        $url = sprintf("%s/%d", $request->getUri(), $group->getId());

        $this->get("logger")->info("Group created", array ("response" => $group));

        return $this->buildJsonResponse($group,
            Response::HTTP_CREATED, array ("Location" => $url));
    }


    /**
     * Gets an existing group or its fields
     *
     * @Rest\Get("/{id}", name="rest_get_group")
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function getGroupAction(int $id, ParamFetcher $paramFetcher) {
        /** @var array */
        $fields = $paramFetcher->get("fields");

        $this->get("logger")->info("Getting an existing group", array ("id" => $id, "fields" => $fields));

        /** @var GroupManagerInterface */
        $manager = $this->get("coloc_matching.core.group_manager");
        /** @var Group */
        $group = empty($fields) ? $manager->read($id) : $manager->read($id, explode(',', $fields));

        $this->get("logger")->info("One group found", array ("id" => $id, "response" => $group));

        if ($group instanceof Visitable) {
            $this->registerVisit($group);
        }

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
     * @throws GroupNotFoundException
     */
    public function updateGroupAction(int $id, Request $request) {
        $this->get("logger")->info("Updating an existing group", array ("id" => $id, "request" => $request->request));

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
     * @throws GroupNotFoundException
     */
    public function deleteGroupAction(int $id) {
        $this->get("logger")->info("Deleting an existing group", array ("id" => $id));

        /** @var GroupManagerInterface */
        $manager = $this->get("coloc_matching.core.group_manager");

        try {
            /** @var Group */
            $group = $manager->read($id);

            if (!empty($group)) {
                $this->get("logger")->info("Group found", array ("group" => $group));

                $manager->delete($group);
            }
        }
        catch (GroupNotFoundException $e) {
            // nothing to do
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
     * @throws GroupNotFoundException
     */
    public function patchGroupAction(int $id, Request $request) {
        $this->get("logger")->info("Patching an existing group", array ("id" => $id, "request" => $request->request));

        return $this->handleUpdateGroupRequest($id, $request, false);
    }


    /**
     * Searches groups by criteria
     *
     * @Rest\Post("/searches", name="rest_search_groups")
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     */
    public function searchGroupsAction(Request $request) {
        $this->get("logger")->info("Searching groups by filtering", array ("request" => $request->request));

        /** @var GroupManagerInterface */
        $manager = $this->get("coloc_matching.core.group_manager");

        /** @var GroupFilter $filter */
        $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(GroupFilterType::class,
            new GroupFilter(), $request->request->all());
        /** @var array */
        $groups = $manager->search($filter);
        /** @var PageResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($groups,
            $manager->countBy($filter), $filter);

        $this->get("logger")->info("Searching groups by filter - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Gets all members of an existing group
     *
     * @Rest\Get("/{id}/members", name="rest_get_group_members")
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function getMembersAction(int $id) {
        $this->get("logger")->info("Getting all members of an existing group", array ("id" => $id));

        /** @var Group $group */
        $group = $this->get("coloc_matching.core.group_manager")->read($id);

        return $this->buildJsonResponse($group->getMembers(), Response::HTTP_OK);
    }


    /**
     * Removes a member from an existing group
     *
     * @Rest\Delete("/{id}/members/{userId}", name="rest_remove_group_member")
     *
     * @param int $id
     * @param int $userId
     * @param Request $request
     *
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function removeMemberAction(int $id, int $userId, Request $request) {
        $this->get("logger")->info("Removing a member of an existing group", array ("id" => $id, "userId" => $userId));

        /** @var GroupManagerInterface $manager */
        $manager = $this->get("coloc_matching.core.group_manager");
        /** @var Group $group */
        $group = $manager->read($id);

        $manager->removeMember($group, $userId);

        return new JsonResponse("Member removed");
    }


    private function handleUpdateGroupRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var GroupManagerInterface */
        $manager = $this->get("coloc_matching.core.group_manager");
        /** @var Group */
        $group = $manager->update($manager->read($id), $request->request->all(), $fullUpdate);

        $this->get("logger")->info("Group updated", array ("response" => $group));

        return $this->buildJsonResponse($group, Response::HTTP_OK);
    }

}
