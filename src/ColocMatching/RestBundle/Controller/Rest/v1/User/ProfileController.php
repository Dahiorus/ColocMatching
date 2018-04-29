<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\DTO\User\ProfileDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for the resource /users/{id}/profile
 *
 * @Rest\Route(path="/users/{id}/profile", requirements={"id"="\d+"}, service="coloc_matching.rest.profile_controller")
 * @Security(expression="has_role('ROLE_USER')")
 *
 * @author Dahiorus
 */
class ProfileController extends AbstractRestController
{
    /** @var UserDtoManagerInterface */
    private $userManager;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, UserDtoManagerInterface $userManager)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);
        $this->userManager = $userManager;
    }


    /**
     * Gets a user's profile
     *
     * @Rest\Get(name="rest_get_user_profile")
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function getProfileAction(int $id)
    {
        $this->logger->info("Getting a User's profile", array ("id" => $id));

        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        /** @var ProfileDto $profile */
        $profile = $this->userManager->getProfile($user);

        $this->logger->info("User's profile found", array ("response" => $profile));

        return $this->buildJsonResponse($profile, Response::HTTP_OK);
    }


    /**
     * Updates the profile of an existing user
     *
     * @Rest\Put(name="rest_update_user_profile")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function updateProfileAction(int $id, Request $request)
    {
        $this->logger->info("Putting a user's profile", array ("id" => $id, "putParams" => $request->request->all()));

        return $this->handleUpdateProfileRequest($id, $request, true);
    }


    /**
     * Updates (partial) the profile of an existing user
     *
     * @Rest\Patch(name="rest_patch_user_profile")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function patchProfileAction(int $id, Request $request)
    {
        $this->logger->info("Patching a user's profile",
            array ("id" => $id, "patchParams" => $request->request->all()));

        return $this->handleUpdateProfileRequest($id, $request, false);
    }


    /**
     * Handles update request
     *
     * @param int $id The user identifier
     * @param Request $request The request to handle
     * @param bool $fullUpdate true if the request is a PUT, otherwise false
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    private function handleUpdateProfileRequest(int $id, Request $request, bool $fullUpdate)
    {
        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        /** @var Profile $profile */
        $profile = $this->userManager->updateProfile($user, $request->request->all(), $fullUpdate);

        $this->logger->info("Profile updated", array ("response" => $profile));

        return $this->buildJsonResponse($profile, Response::HTTP_OK);
    }

}
