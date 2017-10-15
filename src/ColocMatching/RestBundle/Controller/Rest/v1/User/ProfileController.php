<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserManagerInterface;
use ColocMatching\RestBundle\Controller\Response\EntityResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\User\ProfileControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for the resource /users/{id}/profile
 *
 * @Rest\Route("/users/{id}/profile")
 * @Security(expression="has_role('ROLE_USER')")
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
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($user->getProfile());

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
        /** @var Profile $profile */
        $profile = $manager->updateProfile($user, $request->request->all(), $fullUpdate);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($profile);

        $this->get("logger")->info("Profile updated", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }
}