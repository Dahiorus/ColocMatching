<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\Controller\Response\EntityResponse;
use ColocMatching\CoreBundle\Controller\Rest\RestController;
use ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\User\ProfileControllerInterface;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for the resource /users/{id}/profile
 *
 * @Rest\Route("/users/{id}/profile")
 *
 * @author Dahiorus
 */
class ProfileController extends RestController implements ProfileControllerInterface {

    /**
     * Gets a user's profile
     *
     * @Rest\Get("", name="rest_get_user_profile")
     *
     * @param int $id
     *
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

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Updates the profile of an existing user
     *
     * @Rest\Put("", name="rest_update_user_profile")
     *
     * @param int $id
     * @param Request $request
     *
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
     * @Rest\Patch("", name="rest_patch_user_profile")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function patchProfileAction(int $id, Request $request) {
        $this->get("logger")->info("Patching a user's profile", array ("id" => $id, "request" => $request));

        return $this->handleUpdateProfileRequest($id, $request, false);
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

            return $this->buildJsonResponse($response, Response::HTTP_OK);
        } catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a user's profile",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }
}