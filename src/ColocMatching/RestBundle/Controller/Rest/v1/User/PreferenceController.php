<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\DTO\User\AnnouncementPreferenceDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\User\UserPreferenceDto;
use ColocMatching\CoreBundle\Entity\User\AnnouncementPreference;
use ColocMatching\CoreBundle\Entity\User\UserPreference;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\User\AnnouncementPreferenceDtoForm;
use ColocMatching\CoreBundle\Form\Type\User\UserPreferenceDtoForm;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for resource /users
 *
 * @Rest\Route(path="/users/{id}/preferences", requirements={"id"="\d+"},
 *   service="coloc_matching.rest.preference_controller")
 * @Security(expression="has_role('ROLE_USER')")
 *
 * @author Dahiorus
 */
class PreferenceController extends AbstractRestController
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
     * Gets a user's profile search preference
     *
     * @Rest\Get("/user", name="rest_get_user_user_preference")
     *
     * @Operation(tags={ "User - preferences" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(response=200, description="Preferences found", @Model(type=UserPreferenceDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No user found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function getUserPreferenceAction(int $id)
    {
        $this->logger->info("Getting a User's profile preference", array ("id" => $id));

        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        $preference = $this->userManager->getUserPreference($user);

        $this->logger->info("User's user preference found", array ("response" => $preference));

        return $this->buildJsonResponse($preference, Response::HTTP_OK);
    }


    /**
     * Updates the user's profile search preference
     *
     * @Rest\Put("/user", name="rest_update_user_user_preference")
     *
     * @Operation(tags={ "User - preferences" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(name="profile", in="body", required=true, description="The preference to update",
     *     @Model(type=UserPreferenceDtoForm::class)),
     *   @SWG\Response(response=200, description="Preferences updated", @Model(type=UserPreferenceDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No user found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function updateUserPreferenceAction(int $id, Request $request)
    {
        $this->logger->info("Putting a user's profile preference",
            array ("id" => $id, "putParams" => $request->request->all()));

        return $this->handleUpdateUserPreferenceRequest($id, $request, true);
    }


    /**
     * Updates (partial) the user's profile search preference
     *
     * @Rest\Patch("/user", name="rest_patch_user_user_preference")
     *
     * @Operation(tags={ "User - preferences" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(name="profile", in="body", required=true, description="The preference to update",
     *     @Model(type=UserPreferenceDtoForm::class)),
     *   @SWG\Response(response=200, description="Preferences updated", @Model(type=UserPreferenceDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No user found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function patchUserPreferenceAction(int $id, Request $request)
    {
        $this->logger->info("Patching a user's profile preference",
            array ("id" => $id, "patchParams" => $request->request->all()));

        return $this->handleUpdateUserPreferenceRequest($id, $request, false);
    }


    /**
     * Gets a user's announcement search preference
     *
     * @Rest\Get("/announcement", name="rest_get_user_announcement_preference")
     *
     * @Operation(tags={ "User - preferences" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(response=200, description="Preferences found", @Model(type=AnnouncementPreferenceDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No user found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function getAnnouncementPreferenceAction(int $id)
    {
        $this->logger->info("Getting a User's announcement preference", array ("id" => $id));

        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        $preference = $this->userManager->getAnnouncementPreference($user);

        $this->logger->info("User's announcement preference found",
            array ("response" => $preference));

        return $this->buildJsonResponse($preference, Response::HTTP_OK);
    }


    /**
     * Updates the user's announcement search preferences
     *
     * @Rest\Put("/announcement", name="rest_update_user_announcement_preference")
     *
     * @Operation(tags={ "User - preferences" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(name="profile", in="body", required=true, description="The preference to update",
     *     @Model(type=AnnouncementPreferenceDtoForm::class)),
     *   @SWG\Response(response=200, description="Preferences updated", @Model(type=AnnouncementPreferenceDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No user found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function updateAnnouncementPreferenceAction(int $id, Request $request)
    {
        $this->logger->info("Putting a user's announcement preference",
            array ("id" => $id, "putParams" => $request->request->all()));

        return $this->handleUpdateAnnouncementPreferenceRequest($id, $request, true);
    }


    /**
     * Updates (partial) the user's announcement search preference
     *
     * @Rest\Patch("/announcement", name="rest_patch_user_announcement_preference")
     *
     * @Operation(tags={ "User - preferences" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(name="profile", in="body", required=true, description="The preference to update",
     *     @Model(type=AnnouncementPreferenceDtoForm::class)),
     *   @SWG\Response(response=200, description="Preferences updated", @Model(type=AnnouncementPreferenceDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No user found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function patchAnnouncementPreferenceAction(int $id, Request $request)
    {
        $this->logger->info("Patching a user's announcement preference",
            array ("id" => $id, "patchParams" => $request->request->all()));

        return $this->handleUpdateAnnouncementPreferenceRequest($id, $request, false);
    }


    /**
     * Handles the update operation on the user's user preferences
     *
     * @param int $id The user identifier
     * @param Request $request The current request
     * @param bool $fullUpdate If the operation is a patch or a full update
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    private function handleUpdateUserPreferenceRequest(int $id, Request $request, bool $fullUpdate)
    {
        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        /** @var UserPreference $preference */
        $preference = $this->userManager->updateUserPreference($user, $request->request->all(), $fullUpdate);

        $this->logger->info("Profile preference updated", array ("response" => $preference));

        return $this->buildJsonResponse($preference, Response::HTTP_OK);
    }


    /**
     * Handles the update operation on the user's announcement preferences
     *
     * @param int $id The user identifier
     * @param Request $request The current request
     * @param bool $fullUpdate If the operation is a patch or a full update
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    private function handleUpdateAnnouncementPreferenceRequest(int $id, Request $request, bool $fullUpdate)
    {
        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        /** @var AnnouncementPreference $preference */
        $preference = $this->userManager->updateAnnouncementPreference($user, $request->request->all(), $fullUpdate);

        $this->logger->info("Announcement preference updated", array ("response" => $preference));

        return $this->buildJsonResponse($preference, Response::HTTP_OK);
    }
}
