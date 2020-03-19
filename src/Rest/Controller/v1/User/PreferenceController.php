<?php

namespace App\Rest\Controller\v1\User;

use App\Core\DTO\User\AnnouncementPreferenceDto;
use App\Core\DTO\User\UserDto;
use App\Core\DTO\User\UserPreferenceDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Form\Type\User\AnnouncementPreferenceDtoForm;
use App\Core\Form\Type\User\UserPreferenceDtoForm;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Rest\Controller\v1\AbstractRestController;
use App\Rest\Security\Authorization\Voter\UserVoter;
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
 * @Rest\Route(path="/users/{id}/preferences", requirements={"id"="\d+"})
 * @Security(expression="is_granted('ROLE_USER')")
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
        $this->logger->debug("Getting a user's profile preference", array ("id" => $id));

        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        $this->evaluateUserAccess(UserVoter::PREFERENCE_GET, $user);

        $preference = $this->userManager->getUserPreference($user);

        $this->logger->info("User's profile preference found", array ("response" => $preference));

        return $this->buildJsonResponse($preference, Response::HTTP_OK);
    }


    /**
     * Updates the user's profile search preference
     *
     * @Rest\Put("/user", name="rest_update_user_user_preference")
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
     *   @SWG\Response(response=400, description="Validation error")
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
        $this->logger->debug("Updating a user's profile preference",
            array ("id" => $id, "params" => $request->request->all()));

        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        $this->evaluateUserAccess(UserVoter::PREFERENCE_UPDATE, $user);

        /** @var UserPreferenceDto $preference */
        $preference = $this->userManager->updateUserPreference(
            $user, $request->request->all(), $request->isMethod("PUT"));

        $this->logger->info("Announcement preference updated", array ("response" => $preference));

        return $this->buildJsonResponse($preference, Response::HTTP_OK);
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
        $this->logger->debug("Getting a User's announcement preference", array ("id" => $id));

        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        $this->evaluateUserAccess(UserVoter::PREFERENCE_GET, $user);

        $preference = $this->userManager->getAnnouncementPreference($user);

        $this->logger->info("User's announcement preference found", array ("response" => $preference));

        return $this->buildJsonResponse($preference, Response::HTTP_OK);
    }


    /**
     * Updates the user's announcement search preference
     *
     * @Rest\Put("/announcement", name="rest_update_user_announcement_preference")
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
     *   @SWG\Response(response=400, description="Validation error")
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
        $this->logger->debug("Updating a user's announcement preference",
            array ("id" => $id, "params" => $request->request->all()));

        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        $this->evaluateUserAccess(UserVoter::PREFERENCE_UPDATE, $user);

        /** @var AnnouncementPreferenceDto $preference */
        $preference = $this->userManager->updateAnnouncementPreference(
            $user, $request->request->all(), $request->isMethod("PUT"));

        $this->logger->info("Announcement preference updated", array ("response" => $preference));

        return $this->buildJsonResponse($preference, Response::HTTP_OK);
    }

}
