<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\Entity\User\AnnouncementPreference;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserPreference;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserManagerInterface;
use ColocMatching\RestBundle\Controller\Response\EntityResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\User\PreferenceControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for resource /users
 *
 * @Rest\Route("/users/{id}/preferences")
 * @Security(expression="has_role('ROLE_USER')")
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
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($user->getUserPreference());

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
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse(
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
        /** @var UserPreference $preference */
        $preference = $manager->updateUserPreference($user, $request->request->all(), $fullUpdate);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($preference);

        $this->get('logger')->info("Profile preference updated", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    private function handleUpdateAnnouncementPreferenceRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");
        /** @var User */
        $user = $manager->read($id);
        /** @var AnnouncementPreference $preference */
        $preference = $manager->updateAnnouncementPreference($user, $request->request->all(), $fullUpdate);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($preference);

        $this->get("logger")->info("Announcement preference updated", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }
}
