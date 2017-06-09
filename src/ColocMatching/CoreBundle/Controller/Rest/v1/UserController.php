<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Controller\Response\EntityResponse;
use ColocMatching\CoreBundle\Controller\Response\PageResponse;
use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\UserControllerInterface;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Form\Type\Filter\UserFilterType;
use ColocMatching\CoreBundle\Manager\User\UserManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for resource /users
 *
 * @Rest\Route("/users")
 *
 * @author brondon.ung
 */
class UserController extends Controller implements UserControllerInterface {


    /**
     * Lists users or fields with pagination
     *
     * @Rest\Get("", name="rest_get_users")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+", default=RequestConstants::DEFAULT_PAGE)
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+", default=RequestConstants::DEFAULT_LIMIT)
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results", default=RequestConstants::DEFAULT_SORT)
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$", default=RequestConstants::DEFAULT_ORDER)
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return for each result")
     *
     * @param ParamFetcher $paramFetcher
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
        /** @var AbstractFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($page, $limit, $order, $sort);
        /** @var array */
        $users = (empty($fields)) ? $manager->list($filter) : $manager->list($filter, explode(",", $fields));
        /** @var PageResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($users, $manager->countAll(),
            $filter);

        $this->get("logger")->info("Listing users - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Creates a new User
     *
     * @Rest\Post("", name="rest_create_user")
     *
     * @param Request $request
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
            $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($user, $url);

            $this->get("logger")->info("User created", array ("response" => $response));

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response,
                Response::HTTP_CREATED, array ("Location" => $url));
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to create a user",
                array ("request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
        }
    }


    /**
     * Gets a user or its fields by id
     *
     * @Rest\Get("/{id}", name="rest_get_user")
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return")
     *
     * @param int $id
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
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($user);

        $this->get("logger")->info("One user found", array ("response" => $response));

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Updates an existing user
     *
     * @Rest\Put("/{id}", name="rest_update_user")
     *
     * @param int $id
     * @param Request $request
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
     * @param Request $request
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
     * @return JsonResponse
     * @throws InvalidFormDataException
     */
    public function searchUsersAction(Request $request) {
        $this->get("logger")->info("Searching users by filtering", array ("request" => $request));

        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");

        try {
            /** @var UserFilter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(UserFilterType::class,
                new UserFilter(), $request->request->all());
            /** @var array */
            $users = $manager->search($filter);
            /** @var PageResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($users,
                $manager->countBy($filter), $filter);

            $this->get("logger")->info("Searching users by filtering - result information",
                array ("filter" => $filter, "response" => $response));

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response,
                ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to search users",
                array ("request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
        }
    }


    /**
     * Gets a user's announcement
     *
     * @Rest\Get("/{id}/announcement", name="rest_get_user_announcement")
     *
     * @param int $id
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function getAnnouncementAction(int $id) {
        $this->get("logger")->info("Getting a user's announcement", array ("id" => $id));

        /** @var User */
        $user = $this->get("coloc_matching.core.user_manager")->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($user->getAnnouncement());

        $this->get("logger")->info("User's announcement found", array ("response" => $response));

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Gets a user's picture
     *
     * @Rest\Get("/{id}/picture", name="rest_get_user_picture")
     *
     * @param int $id
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function getPictureAction(int $id) {
        $this->get("logger")->info("Getting a user's picture", array ("id" => $id));

        /** @var User */
        $user = $this->get("coloc_matching.core.user_manager")->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($user->getPicture());

        $this->get("logger")->info("User's picture found", array ("response" => $response));

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Uploads a file as the profile picture of an existing user
     *
     * @Rest\Post("/{id}/picture", name="rest_upload_user_picture")
     * @Rest\FileParam(name="file", image=true, nullable=false, description="The picture to upload")
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function uploadPictureAction(int $id, Request $request) {
        $this->get("logger")->info("Uploading a profile picture for a user", array ("id" => $id, "request" => $request));

        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");
        /** @var User */
        $user = $manager->read($id);

        try {
            /** @var ProfilePicture */
            $picture = $manager->uploadProfilePicture($user, $request->files->get("file"));
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($picture);

            $this->get("logger")->info("Profie picture uploaded", array ("response" => $response));

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to upload a profile picture for a user",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
        }
    }


    /**
     * Deletes the profile picture of an existing user
     *
     * @Rest\Delete("/{id}/picture", name="rest_delete_user_picture")
     *
     * @param int $id
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function deletePictureAction(int $id) {
        /** @var UserManagerInterface */
        $manager = $this->get('coloc_matching.core.user_manager');

        $this->get('logger')->info("Deleting a User's profile picture", array ("id" => $id));

        /** @var User */
        $user = $manager->read($id);

        if (!empty($user->getPicture())) {
            $this->get('logger')->info("User found", array ("user" => $user));

            $manager->deleteProfilePicture($user);
        }

        return new JsonResponse("User's profile picture deleted", Response::HTTP_OK);
    }


    /**
     * Gets a user's profile
     *
     * @Rest\Get("/{id}/profile", name="rest_get_user_profile")
     *
     * @param int $id
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function getProfileAction(int $id) {
        $this->get("logger")->info("Getting a User's profile", array ("id" => $id));

        /** @var User */
        $user = $this->get("coloc_matching.core.user_manager")->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($user->getProfile());

        $this->get("logger")->info("User's profile found [id: %d | profile: %s]", array ("response" => $response));

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Updates the profile of an existing user
     *
     * @Rest\Put("/{id}/profile", name="rest_update_user_profile")
     *
     * @param int $id
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function updateProfileAction(int $id, Request $request) {
        $this->get("logger")->info("Putting a user's profile", array ("id" => $id, "request" => $request));

        return $this->handleUpdateProfileRequest($id, $request, true);
    }


    /**
     * Updates (partial) the profile of an existing user
     *
     * @Rest\Patch("/{id}/profile", name="rest_patch_user_profile")
     *
     * @param int $id
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function patchProfileAction(int $id, Request $request) {
        $this->get("logger")->info("Patching a user's profile", array ("id" => $id, "request" => $request));

        return $this->handleUpdateProfileRequest($id, $request, false);
    }


    /**
     * Gets a user's user search preference
     *
     * @Rest\Get("/{id}/preferences/user", name="rest_get_user_user_preference")
     *
     * @param int $id
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function getUserPreferenceAction(int $id) {
        $this->get("logger")->info("Getting a User's profile preference", array ("id" => $id));

        /** @var User */
        $user = $this->get("coloc_matching.core.user_manager")->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($user->getUserPreference());

        $this->get("logger")->info("User's user preference found", array ("response" => $response));

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Updates the user search preference of an existing user
     *
     * @Rest\Put("/{id}/preferences/user", name="rest_update_user_user_preference")
     *
     * @param int $id
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function updateUserPreferenceAction(int $id, Request $request) {
        $this->get("logger")->info("Putting a user's profile preference", array ("id" => $id, "request" => $request));

        return $this->handleUpdateUserPreferenceRequest($id, $request, true);
    }


    /**
     * Updates (partial) the user search preference of an existing user
     *
     * @Rest\Patch("/{id}/preferences/user", name="rest_patch_user_user_preference")
     *
     * @param int $id
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function patchUserPreferenceAction(int $id, Request $request) {
        $this->get("logger")->info("Patching a user's profile preference", array ("id" => $id, "request" => $request));

        return $this->handleUpdateUserPreferenceRequest($id, $request, false);
    }


    /**
     * Gets a user's announcement search preference
     *
     * @Rest\Get("/{id}/preferences/announcement", name="rest_get_user_announcement_preference")
     *
     * @param int $id
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function getAnnouncementPreferenceAction(int $id) {
        $this->get("logger")->info("Getting a User's announcement preference", array ("id" => $id));

        /** @var User */
        $user = $this->get('coloc_matching.core.user_manager')->read($id);
        /** @var RestDataResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createRestDataResponse(
            $user->getAnnouncementPreference());

        $this->get('logger')->info(
            sprintf("User's announcement preference found [id: %d, user preference: %s]", $user->getId(),
                $user->getUserPreference()), [ 'response' => $response]);

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Updates the announcement search preference of an existing user
     *
     * @Rest\Put("/{id}/preferences/announcement", name="rest_update_user_announcement_preference")
     *
     * @param int $id
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function updateAnnouncementPreferenceAction(int $id, Request $request) {
        $this->get("logger")->info("Putting a user's announcement preference",
            array ("id" => $id, "request" => $request));

        return $this->handleUpdateAnnouncementPreferenceRequest($id, $request, true);
    }


    /**
     * Updates (partial) the announcement search preference of an existing user
     *
     * @Rest\Patch("/{id}/preferences/announcement", name="rest_patch_user_announcement_preference")
     *
     * @param int $id
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function patchAnnouncementPreferenceAction(int $id, Request $request) {
        $this->get("logger")->info("Patching a user's announcement preference",
            array ("id" => $id, "request" => $request));

        return $this->handleUpdateAnnouncementPreferenceRequest($id, $request, false);
    }


    private function handleUpdateUserRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");
        /** @var User */
        $user = $manager->read($id);

        try {
            $user = $manager->update($user, $request->request->all(), $fullUpdate);
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($user);

            $this->get("logger")->info("User updated", array ("response" => $response));

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a user",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
        }
    }


    private function handleUpdateProfileRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");
        /** @var User */
        $user = $manager->read($id);

        try {
            $profile = $manager->updateProfile($user, $request->request->all(), $fullUpdate);
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($profile);

            $this->get("logger")->info("Profile updated", array ("response" => $response));

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a user's profile",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
        }
    }


    private function handleUpdateUserPreferenceRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");
        /** @var User */
        $user = $manager->read($id);

        try {
            $preference = $manager->updateUserPreference($user, $request->request->all(), $fullUpdate);
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($preference);

            $this->get('logger')->info("Profile preference updated", array ("response" => $response));

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a user's user preference",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
        }
    }


    private function handleUpdateAnnouncementPreferenceRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");
        /** @var User */
        $user = $manager->read($id);

        try {
            $preference = $manager->updateAnnouncementPreference($user, $request->request->all(), $fullUpdate);
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($preference);

            $this->get("logger")->info("Announcement preference updated", array ("response" => $response));

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a user's announcement preference",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
        }
    }

}
