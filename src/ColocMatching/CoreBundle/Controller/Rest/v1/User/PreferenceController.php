<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\Controller\Response\EntityResponse;
use ColocMatching\CoreBundle\Controller\Rest\RestController;
use ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\User\PreferenceControllerInterface;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for resource /users
 *
 * @Rest\Route("/users/{id}/preferences")
 *
 * @author Dahiorus
 */
class PreferenceController extends RestController implements PreferenceControllerInterface {

    /**
     * Gets a user's user search preference
     *
     * @Rest\Get("/user", name="rest_get_user_user_preference")
     *
     * @param int $id
     *
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

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Updates the user search preference of an existing user
     *
     * @Rest\Put("/user", name="rest_update_user_user_preference")
     *
     * @param int $id
     * @param Request $request
     *
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
     * @Rest\Patch("/user", name="rest_patch_user_user_preference")
     *
     * @param int $id
     * @param Request $request
     *
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
     * @Rest\Get("/announcement", name="rest_get_user_announcement_preference")
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function getAnnouncementPreferenceAction(int $id) {
        $this->get("logger")->info("Getting a User's announcement preference", array ("id" => $id));

        /** @var User */
        $user = $this->get('coloc_matching.core.user_manager')->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse(
            $user->getAnnouncementPreference());

        $this->get('logger')->info(
            sprintf("User's announcement preference found [id: %d, user preference: %s]", $user->getId(),
                $user->getUserPreference()), ['response' => $response]);

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Updates the announcement search preference of an existing user
     *
     * @Rest\Put("/announcement", name="rest_update_user_announcement_preference")
     *
     * @param int $id
     * @param Request $request
     *
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
     * @Rest\Patch("/announcement", name="rest_patch_user_announcement_preference")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function patchAnnouncementPreferenceAction(int $id, Request $request) {
        $this->get("logger")->info("Patching a user's announcement preference",
            array ("id" => $id, "request" => $request));

        return $this->handleUpdateAnnouncementPreferenceRequest($id, $request, false);
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

            return $this->buildJsonResponse($response, Response::HTTP_OK);
        } catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a user's user preference",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
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

            return $this->buildJsonResponse($response, Response::HTTP_OK);
        } catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a user's announcement preference",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }
}