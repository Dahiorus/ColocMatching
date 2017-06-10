<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\GroupControllerInterface;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Manager\Group\GroupManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use ColocMatching\CoreBundle\Exception\GroupNotFoundException;
use ColocMatching\CoreBundle\Controller\Response\EntityResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use ColocMatching\CoreBundle\Form\Type\Filter\GroupFilterType;
use ColocMatching\CoreBundle\Controller\Response\PageResponse;
use ColocMatching\CoreBundle\Entity\Group\GroupPicture;

/**
 * REST Controller for the resource /groups
 *
 * @Rest\Route("/groups")
 *
 * @author Dahiorus
 */
class GroupController extends Controller implements GroupControllerInterface {


    /**
     * Lists groups or fields with pagination
     *
     * @Rest\Get("", name="rest_get_groups")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+", default=RequestConstants::DEFAULT_PAGE)
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+", default=RequestConstants::DEFAULT_LIMIT)
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results", default=RequestConstants::DEFAULT_SORT)
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$", default=RequestConstants::DEFAULT_ORDER)
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return for each result")
     *
     * @param Request $paramFetcher
     * @return JsonResponse
     */
    public function getGroupsAction(ParamFetcher $paramFetcher) {
        $pageable = $this->get("coloc_matching.core.controller_utils")->extractPageableParameters($paramFetcher);
        $fields = $paramFetcher->get("fields");

        $this->get("logger")->info("Listing groups", array ("pagination" => $pageable, "fields" => $fields));

        /** @var PageableFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($pageable["page"],
            $pageable["limit"], $pageable["order"], $pageable["sort"]);
        /** @var GroupManagerInterface */
        $manager = $this->get("coloc_matching.core.group_manager");
        /** @var array */
        $groups = empty($fields) ? $manager->list($filter) : $manager->list($filter, explode(",", $fields));
        /** @var PageResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($groups,
            $manager->countAll(), $filter);

        $this->get("logger")->info("Listing groups - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response,
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
     * @return JsonResponse
     * @throws JWTDecodeFailureException
     * @throws UnprocessableEntityHttpException
     */
    public function createGroupAction(Request $request) {
        /** @var User */
        $user = $this->get("coloc_matching.core.controller_utils")->extractUser($request);

        $this->get("logger")->info("Posting a new group", array ("user" => $user, "request" => $request));

        try {
            /** @var Group */
            $group = $this->get("coloc_matching.core.group_manager")->create($user, $request->request->all());
            /** @var string */
            $url = sprintf("%s/%d", $request->getUri(), $group->getId());
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($group, $url);

            $this->get("logger")->info("Group created", array ("response" => $response));

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response,
                Response::HTTP_CREATED, array ("Location" => $url));
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to create a group",
                array ("request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
        }
    }


    /**
     * Gets an existing group or its fields
     *
     * @Rest\Get("/{id}", name="rest_get_group")
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
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
        $group = (!$fields) ? $manager->read($id) : $manager->read($id, explode(',', $fields));
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($group);

        $this->get("logger")->info("One group found", array ("id" => $id, "response" => $response));

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Updates an existing group
     *
     * @Rest\Put("/{id}", name="rest_update_group")
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function updateGroupAction(int $id, Request $request) {
        $this->get("logger")->info("Updating an existing group", array ("id" => $id, "request" => $request));

        return $this->handleUpdateGroupRequest($id, $request, true);
    }


    /**
     * Deletes an existing group
     *
     * @Rest\Delete("/{id}", name="rest_delete_group")
     *
     * @param int $id
     * @param Request $request
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
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function patchGroupAction(int $id, Request $request) {
        $this->get("logger")->info("Patching an existing group", array ("id" => $id, "request" => $request));

        return $this->handleUpdateGroupRequest($id, $request, false);
    }


    /**
     * Searches groups by criteria
     *
     * @Rest\Post("/searches", name="rest_search_groups")
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidFormDataException
     */
    public function searchGroupsAction(Request $request) {
        $this->get("logger")->info("Searching groups by filtering", array ("request" => $request));

        /** @var GroupManagerInterface */
        $manager = $this->get("coloc_matching.core.group_manager");

        try {
            /** @var GroupFilter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(GroupFilterType::class,
                new GroupFilter(), $request->request->all());
            /** @var array */
            $groups = $manager->search($filter);
            /** @var PageResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($groups,
                $manager->countBy($filter), $filter);

            $this->get("logger")->info("Searching groups by filter - result information",
                array ("filter" => $filter, "response" => $response));

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response,
                ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to search groups",
                array ("request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
        }
    }


    /**
     * Gets all members of an existing group
     *
     * @Rest\Get("/{id}/members", name="rest_get_group_members")
     *
     * @param int $id
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function getMembersAction(int $id) {
        $this->get("logger")->info("Getting all members of an existing group", array ("id" => $id));

        /** @var Group */
        $group = $this->get("coloc_matching.core.group_manager")->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($group->getMembers());

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Removes a member from an existing group
     *
     * @Rest\Delete("/{id}/members/{userId}", name="rest_remove_group_member")
     *
     * @param int $id
     * @param int $userId
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function removeMemberAction(int $id, int $userId) {
        $this->get("logger")->info("Removing a member of an existing group", array ("id" => $id, "userId" => $userId));

        /** @var GroupManagerInterface */
        $manager = $this->get("coloc_matching.core.group_manager");

        $manager->removeMember($manager->read($id), $userId);

        return new JsonResponse("Member removed");
    }


    private function handleUpdateGroupRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var GroupManagerInterface */
        $manager = $this->get("coloc_matching.core.group_manager");
        /** @var Group */
        $group = $manager->read($id);

        try {
            $group = $manager->update($group, $request->request->all(), $fullUpdate);
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($group);

            $this->get("logger")->info("Group updated", array ("response" => $response));

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a group",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
        }
    }


    /**
     * Gets a group's picture
     *
     * @Rest\Get("/{id}/picture", name="rest_get_group_picture")
     *
     * @param int $id
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function getGroupPictureAction(int $id) {
        $this->get("logger")->info("Getting a group's picture", array ("id" => $id));

        /** @var Group */
        $group = $this->get("coloc_matching.core.group_manager")->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($group->getPicture());

        $this->get("logger")->info("Group's picture found", array ("response" => $response));

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Uploads a file as the picture of an existing group
     *
     * @Rest\Post("/{id}/picture", name="rest_upload_group_picture")
     * @Rest\FileParam(name="file", image=true, nullable=false, description="The picture to upload")
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function uploadGroupPictureAction(int $id, Request $request) {
        $this->get("logger")->info("Uploading a picture for a group", array ("id" => $id, "request" => $request));

        /** @var GroupManagerInterface */
        $manager = $this->get("coloc_matching.core.group_manager");
        /** @var Group */
        $group = $manager->read($id);

        try {
            /** @var GroupPicture */
            $picture = $manager->uploadGroupPicture($group, $request->files->get("file"));
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($picture);

            $this->get("logger")->info("Group picture uploaded", array ("response" => $response));

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to upload a picture for a group",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
        }
    }


    /**
     * Deletes the picture of an existing group
     *
     * @Rest\Delete("/{id}/picture", name="rest_delete_group_picture")
     *
     * @param int $id
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function deleteGroupPictureAction(int $id) {
        /** @var GroupManagerInterface */
        $manager = $this->get('coloc_matching.core.group_manager');

        $this->get("logger")->info("Deleting a group's picture", array ("id" => $id));

        $manager->deleteGroupPicture($manager->read($id));

        return new JsonResponse("Group's picture deleted");
    }

}