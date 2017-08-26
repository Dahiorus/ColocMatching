<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Form\Type\Filter\UserFilterType;
use ColocMatching\CoreBundle\Manager\User\UserManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use ColocMatching\RestBundle\Controller\Response\EntityResponse;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\v1\Swagger\User\UserControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for resource /users
 *
 * @Rest\Route("/users")
 *
 * @author Dahiorus
 */
class UserController extends RestController implements UserControllerInterface {

    /**
     * Lists users or fields with pagination
     *
     * @Rest\Get("", name="rest_get_users")
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
    public function getUsersAction(ParamFetcher $paramFetcher) {
        $page = $paramFetcher->get("page", true);
        $limit = $paramFetcher->get("size", true);
        $order = $paramFetcher->get("order", true);
        $sort = $paramFetcher->get("sort", true);
        $fields = $paramFetcher->get("fields");

        $this->get("logger")->info("Listing users",
            array ("page" => $page, "size" => $limit, "order" => $order, "sort" => $sort, "fields" => $fields));

        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");
        /** @var PageableFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($page, $limit, $order, $sort);
        /** @var array */
        $users = empty($fields) ? $manager->list($filter) : $manager->list($filter, explode(",", $fields));
        /** @var PageResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($users, $manager->countAll(),
            $filter);

        $this->get("logger")->info("Listing users - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Creates a new User
     *
     * @Rest\Post("", name="rest_create_user")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createUserAction(Request $request) {
        $this->get('logger')->info("Posting a new user", array ("request" => $request));

        try {
            /** @var User */
            $user = $this->get('coloc_matching.core.user_manager')->create($request->request->all());
            /** @var string */
            $url = sprintf("%s/%d", $request->getUri(), $user->getId());
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($user, $url);

            $this->get("logger")->info("User created", array ("response" => $response));

            return $this->buildJsonResponse($response,
                Response::HTTP_CREATED, array ("Location" => $url));
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to create a user",
                array ("request" => $request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }


    /**
     * Gets a user or its fields by id
     *
     * @Rest\Get("/{id}", name="rest_get_user")
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function getUserAction(int $id, ParamFetcher $paramFetcher) {
        /** @var array */
        $fields = $paramFetcher->get("fields");

        $this->get('logger')->info("Getting an existing user", array ("id" => $id, "fields" => $fields));

        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");
        /** @var User */
        $user = (empty($fields)) ? $manager->read($id) : $manager->read($id, explode(",", $fields));
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($user);

        $this->get("logger")->info("One user found", array ("response" => $response));

        if ($user instanceof Visitable) {
            $this->registerVisit($user);
        }

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Updates an existing user
     *
     * @Rest\Put("/{id}", name="rest_update_user")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateUserAction(int $id, Request $request) {
        $this->get("logger")->info("Putting an existing user", array ("id" => $id, "request" => $request));

        return $this->handleUpdateUserRequest($id, $request, true);
    }


    /**
     * Updates (partial) an existing user
     *
     * @Rest\Patch("/{id}", name="rest_patch_user")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function patchUserAction(int $id, Request $request) {
        $this->get("logger")->info("Patching an existing user", array ("id" => $id, "request" => $request));

        return $this->handleUpdateUserRequest($id, $request, false);
    }


    /**
     * Deletes an existing user
     *
     * @Rest\Delete("/{id}", name="rest_delete_user")
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteUserAction(int $id) {
        $this->get("logger")->info("Deleting an existing user", array ("id" => $id));

        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");

        try {
            /** @var User */
            $user = $manager->read($id);

            if (!empty($user)) {
                $this->get("logger")->info("User found", array ("user" => $user));

                $manager->delete($user);
            }
        }
        catch (UserNotFoundException $e) {
            // nothing to do
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
     * @throws InvalidFormDataException
     */
    public function searchUsersAction(Request $request) {
        $this->get("logger")->info("Searching users by filtering", array ("request" => $request));

        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");

        try {
            /** @var UserFilter $filter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(UserFilterType::class,
                new UserFilter(), $request->request->all());
            /** @var array */
            $users = $manager->search($filter);
            /** @var PageResponse */
            $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($users,
                $manager->countBy($filter), $filter);

            $this->get("logger")->info("Searching users by filtering - result information",
                array ("filter" => $filter, "response" => $response));

            return $this->buildJsonResponse($response,
                ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to search users",
                array ("request" => $request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }


    /**
     * Gets a user's announcement
     *
     * @Rest\Get("/{id}/announcement", name="rest_get_user_announcement")
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function getAnnouncementAction(int $id) {
        $this->get("logger")->info("Getting a user's announcement", array ("id" => $id));

        /** @var User */
        $user = $this->get("coloc_matching.core.user_manager")->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($user->getAnnouncement());

        $this->get("logger")->info("User's announcement found", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    private function handleUpdateUserRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");
        /** @var User */
        $user = $manager->read($id);

        try {
            $user = $manager->update($user, $request->request->all(), $fullUpdate);
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($user);

            $this->get("logger")->info("User updated", array ("response" => $response));

            return $this->buildJsonResponse($response, Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a user",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }

}
