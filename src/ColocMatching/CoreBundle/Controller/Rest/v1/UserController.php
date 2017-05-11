<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Controller\Rest\RestDataResponse;
use ColocMatching\CoreBundle\Controller\Rest\RestListResponse;
use ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\UserControllerInterface;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Form\Type\Filter\UserFilterType;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for resource /users
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

        $this->get('logger')->info(
            sprintf("Getting users [page: %d, limit: %d, order:'%s', sort: '%s', fields: [%s]]", $page, $limit, $order,
                $sort, $fields), [ "paramFetcher" => $paramFetcher]);

        /** @var UserManager */
        $manager = $this->get("coloc_matching.core.user_manager");
        /** @var AbstractFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->setFilter(new UserFilter(), $page, $limit, $order,
            $sort);
        /** @var array */
        $users = (empty($fields)) ? $manager->list($filter) : $manager->list($filter, explode(",", $fields));
        /** @var RestListResponse */
        $restList = $this->get("coloc_matching.core.rest_response_factory")->createRestListResponse($users,
            $manager->countAll(), $filter);
        /** @var int */
        $codeStatus = ($restList->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK;

        $this->get("logger")->info(
            sprintf("Result information [page: %d, size: %d, total: %d]", $restList->getPage(), $restList->getSize(),
                $restList->getTotalElements()), [ "response" => $restList]);

        return new JsonResponse($this->get('jms_serializer')->serialize($restList, 'json'), $codeStatus, [ ], true);
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
        /** @var array */
        $postData = $request->request->all();

        $this->get('logger')->info("Posting a new user", [ 'request' => $request]);

        try {
            /** @var User */
            $user = $this->get('coloc_matching.core.user_manager')->create($postData);
            /** @var string */
            $url = sprintf("%s%d", $request->getUri(), $user->getId());
            /** @var RestDataResponse */
            $restData = $this->get("coloc_matching.core.rest_response_factory")->createRestDataResponse($user, $url);

            $this->get('logger')->info(sprintf("User created [user: %s]", $user), [ 'response' => $restData]);

            return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_CREATED,
                [ "Location" => $url], true);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to create a user",
                [ "request" => $request, "exception" => $e]);

            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ ], true);
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

        $this->get('logger')->info(sprintf("Getting an existing user [id: %d, fields: [%s]]", $id, $fields),
            [ "id" => $id, "paramFetcher" => $paramFetcher]);

        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.user_manager');
        /** @var User */
        $user = (empty($fields)) ? $manager->read($id) : $manager->read($id, explode(",", $fields));
        /** @var RestDataResponse */
        $restData = $this->get("coloc_matching.core.rest_response_factory")->createRestDataResponse($user);

        $this->get('logger')->info(sprintf("One user found with id %d", $id), [ "response" => $restData]);

        return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_OK, [ ], true);
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
        $this->get('logger')->info(sprintf("Putting a user [id: %d]", $id), [ 'id' => $id, 'request' => $request]);

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
        $this->get('logger')->info(sprintf("Patching a user [id: %d]", $id), [ 'id' => $id, 'request' => $request]);

        return $this->handleUpdateUserRequest($id, $request, false);
    }


    /**
     * Deletes an existing user
     *
     * @Rest\Delete("/{id}", name="rest_delete_user")
     * @Security(expression="has_role('ROLE_ADMIN')")
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteUserAction(int $id) {
        $this->get('logger')->info(sprintf("Deleting a User with the following id [id: %d]", $id), [ 'id' => $id]);

        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.user_manager');
        /** @var User */
        $user = $manager->read($id);

        if ($user) {
            $this->get('logger')->info(sprintf("User found [user: %s]", $user));

            $manager->delete($user);
        }

        return new JsonResponse("User deleted", Response::HTTP_OK);
    }


    /**
     * Searches users by criteria
     *
     * @Rest\Post("/searches/", name="rest_search_users")
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidFormDataException
     */
    public function searchUsersAction(Request $request) {
        /** @var array */
        $filterData = $request->request->all();

        $this->get("logger")->info("Searching users by filter", [
            "filterData" => $filterData,
            "request" => $request]);

        /** @var UserManager */
        $manager = $this->get("coloc_matching.core.user_manager");

        try {
            /** @var UserFilter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(UserFilterType::class,
                new UserFilter(), $filterData);
            /** @var array */
            $users = $manager->search($filter);
            /** @var RestListResponse */
            $restList = $this->get("coloc_matching.core.rest_response_factory")->createRestListResponse($users,
                $manager->countBy($filter), $filter);
            /** @var int */
            $codeStatus = ($restList->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK;

            $this->get("logger")->info(
                sprintf("Result information [page: %d, size: %d, total: %d]", $restList->getPage(),
                    $restList->getSize(), $restList->getTotalElements()), [
                    "response" => $restList,
                    "filter" => $filter]);

            return new JsonResponse($this->get("jms_serializer")->serialize($restList, "json"), $codeStatus,
                [ "Location" => $request->getUri()], true);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to search users",
                [ "request" => $request, "exception" => $e]);

            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ "Location" => $request->getUri()],
                true);
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
        $this->get('logger')->info(sprintf("Getting a user's announcement [id: %d]", $id), [ 'id' => $id]);

        /** @var User */
        $user = $this->get('coloc_matching.core.user_manager')->read($id);
        /** @var RestDataResponse */
        $restData = $this->get("coloc_matching.core.rest_response_factory")->createRestDataResponse(
            $user->getAnnouncement());

        $this->get('logger')->info(
            sprintf("User's announcement found [id: %d, announcement: %s]", $user->getId(), $user->getAnnouncement()),
            [ 'response' => $restData]);

        return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_OK, [ ], true);
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
        $this->get('logger')->info(sprintf("Getting a user's picture [id: %d]", $id), [ 'id' => $id]);

        /** @var User */
        $user = $this->get('coloc_matching.core.user_manager')->read($id);
        /** @var RestDataResponse */
        $restData = $this->get("coloc_matching.core.rest_response_factory")->createRestDataResponse($user->getPicture());

        $this->get('logger')->info(
            sprintf("User's picture found [id: %d, picture: %s]", $user->getId(), $user->getPicture()),
            [ 'response' => $restData]);

        return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_OK, [ ], true);
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
        $this->get("logger")->info(sprintf("Uploading a profile picture for a user [id: %d]", $id),
            [ "id" => $id, "request" => $request]);

        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.user_manager');
        /** @var User */
        $user = $manager->read($id);
        /** @var File */
        $file = $request->files->get("file");

        try {
            /** @var ProfilePicture */
            $picture = $manager->uploadProfilePicture($user, $file);
            /** @var RestDataResponse */
            $restData = $this->get("coloc_matching.core.rest_response_factory")->createRestDataResponse(
                $user->getPicture());

            $this->get('logger')->info(sprintf("Profie picture uploaded [picture: %s]", $picture),
                [ 'response' => $restData]);

            return new JsonResponse($this->get("jms_serializer")->serialize($restData, "json"), Response::HTTP_OK,
                [ "Location" => $request->getUri()], true);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to upload a profile picture for a user",
                [ "id" => $id, "request" => $request, "exception" => $e]);

            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ "Location" => $request->getUri()],
                true);
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
        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.user_manager');

        $this->get('logger')->info(sprintf("Deleting a User's profile picture [id: %d]", $id), [ "id" => $id]);

        /** @var User */
        $user = $manager->read($id);

        if (!empty($user->getPicture())) {
            $this->get('logger')->info(sprintf("User found [user: %s]", $user));

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
        $this->get('logger')->info(sprintf("Getting a User's profile [id: %d]", $id), [ 'id' => $id]);

        /** @var User */
        $user = $this->get('coloc_matching.core.user_manager')->read($id);
        /** @var RestDataResponse */
        $restData = $this->get("coloc_matching.core.rest_response_factory")->createRestDataResponse($user->getProfile());

        $this->get('logger')->info(
            sprintf("User's profile found [id: %d | profile: %s]", $user->getId(), $user->getProfile()),
            [ 'response' => $restData]);

        return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_OK, [ ], true);
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
        $this->get('logger')->info(sprintf("Putting a user's profile [id: %d]", $id),
            [ 'id' => $id, 'request' => $request]);

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
        $this->get('logger')->info(sprintf("Patching a user's profile [id: %d]", $id),
            [ 'id' => $id, 'request' => $request]);

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
        $this->get('logger')->info(sprintf("Getting a User's user preference [id: %d]", $id), [ 'id' => $id]);

        /** @var User */
        $user = $this->get('coloc_matching.core.user_manager')->read($id);
        /** @var RestDataResponse */
        $restData = $this->get("coloc_matching.core.rest_response_factory")->createRestDataResponse(
            $user->getUserPreference());

        $this->get('logger')->info(
            sprintf("User's user preference found [id: %d, user preference: %s]", $user->getId(),
                $user->getUserPreference()), [ 'response' => $restData]);

        return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_OK, [ ], true);
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
        $this->get('logger')->info(sprintf("Putting a user's user preference [id: %d]", $id),
            [ 'id' => $id, 'request' => $request]);

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
        $this->get('logger')->info(sprintf("Patching a user's user preference [id: %d]", $id),
            [ 'id' => $id, 'request' => $request]);

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
        $this->get('logger')->info(sprintf("Getting a User's announcement preference [id: %d]", $id), [
            'id' => $id]);

        /** @var User */
        $user = $this->get('coloc_matching.core.user_manager')->read($id);
        /** @var RestDataResponse */
        $restData = $this->get("coloc_matching.core.rest_response_factory")->createRestDataResponse(
            $user->getAnnouncementPreference());

        $this->get('logger')->info(
            sprintf("User's announcement preference found [id: %d, user preference: %s]", $user->getId(),
                $user->getUserPreference()), [ 'response' => $restData]);

        return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_OK, [ ], true);
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
        $this->get('logger')->info(sprintf("Putting a user's announcement preference [id: %d]", $id),
            [ 'id' => $id, 'request' => $request]);

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
        $this->get('logger')->info(sprintf("Patching a user's announcement preference [id: %d]", $id),
            [ 'id' => $id, 'request' => $request]);

        return $this->handleUpdateAnnouncementPreferenceRequest($id, $request, false);
    }


    private function handleUpdateUserRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.user_manager');
        /** @var User */
        $user = $manager->read($id);
        /** @var array */
        $data = $request->request->all();

        try {
            $user = ($fullUpdate) ? $manager->update($user, $data) : $manager->partialUpdate($user, $data);
            /** @var RestDataResponse */
            $restData = $this->get("coloc_matching.core.rest_response_factory")->createRestDataResponse($user);

            $this->get('logger')->info(sprintf("User updated [user: %s]", $user), [ 'response' => $restData]);

            return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_OK, [ ],
                true);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a user",
                [ "id" => $id, "request" => $request, "exception" => $e]);

            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ ], true);
        }
    }


    private function handleUpdateProfileRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.user_manager');
        /** @var User */
        $user = $manager->read($id);
        /** @var array */
        $data = $request->request->all();

        try {
            $profile = ($fullUpdate) ? $manager->updateProfile($user, $data) : $manager->partialUpdateProfile($user,
                $data);
            /** @var RestDataResponse */
            $restData = $this->get("coloc_matching.core.rest_response_factory")->createRestDataResponse($profile);

            $this->get('logger')->info(sprintf("Profile updated [id: %d, profile: %s]", $id, $profile),
                [ 'response' => $restData]);

            return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_OK, [ ],
                true);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a user's profile",
                [ "id" => $id, "request" => $request, "exception" => $e]);

            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ ], true);
        }
    }


    private function handleUpdateUserPreferenceRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.user_manager');
        /** @var User */
        $user = $manager->read($id);
        /** @var array */
        $data = $request->request->all();

        try {
            $preference = ($fullUpdate) ? $manager->updateUserPreference($user, $data) : $manager->partialUpdateUserPreference(
                $user, $data);
            /** @var RestDataResponse */
            $restData = $this->get("coloc_matching.core.rest_response_factory")->createRestDataResponse($preference);

            $this->get('logger')->info(
                sprintf("User preference updated [id: %d, user preference: %s]", $id, $preference),
                [ 'response' => $restData]);

            return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_OK, [ ],
                true);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a user's user preference",
                [ "id" => $id, "request" => $request, "exception" => $e]);

            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ ], true);
        }
    }


    private function handleUpdateAnnouncementPreferenceRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.user_manager');
        /** @var User */
        $user = $manager->read($id);
        /** @var array */
        $data = $request->request->all();

        try {
            $preference = ($fullUpdate) ? $manager->updateAnnouncementPreference($user, $data) : $manager->partialUpdateAnnouncementPreference(
                $user, $data);
            /** @var RestDataResponse */
            $restData = $this->get("coloc_matching.core.rest_response_factory")->createRestDataResponse($preference);

            $this->get('logger')->info(
                sprintf("User preference updated [id: %d, announcement preference: %s]", $id, $preference),
                [ 'response' => $restData]);

            return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'), Response::HTTP_OK, [ ],
                true);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a user's announcement preference",
                [ "id" => $id, "request" => $request, "exception" => $e]);

            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ ], true);
        }
    }

}
