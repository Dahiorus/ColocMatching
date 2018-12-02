<?php

namespace App\Rest\Controller\v1\User;

use App\Core\DTO\User\ProfilePictureDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\User;
use App\Core\Entity\User\UserStatus;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Form\Type\Filter\HistoricAnnouncementFilterForm;
use App\Core\Form\Type\Security\EditPasswordForm;
use App\Core\Form\Type\User\UserDtoForm;
use App\Core\Manager\Announcement\HistoricAnnouncementDtoManagerInterface;
use App\Core\Manager\Message\PrivateConversationDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Manager\Visit\VisitDtoManagerInterface;
use App\Core\Repository\Filter\HistoricAnnouncementFilter;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Security\User\TokenEncoderInterface;
use App\Core\Validator\FormValidator;
use App\Rest\Controller\Response\Announcement\HistoricAnnouncementPageResponse;
use App\Rest\Controller\Response\Message\PrivateConversationPageResponse;
use App\Rest\Controller\Response\PageResponse;
use App\Rest\Controller\Response\Visit\VisitPageResponse;
use App\Rest\Controller\v1\AbstractRestController;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
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
 * REST controller for resources /me
 *
 * @Rest\Route(path="/me")
 * @Security(expression="is_granted('ROLE_USER')")
 *
 * @author Dahiorus
 */
class SelfController extends AbstractRestController
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var HistoricAnnouncementDtoManagerInterface */
    private $historicAnnouncementManager;

    /** @var PrivateConversationDtoManagerInterface */
    private $privateConversationManager;

    /** @var VisitDtoManagerInterface */
    private $visitManager;

    /** @var FormValidator */
    private $formValidator;

    /** @var TokenEncoderInterface */
    private $tokenEncoder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, UserDtoManagerInterface $userManager,
        HistoricAnnouncementDtoManagerInterface $historicAnnouncementManager,
        PrivateConversationDtoManagerInterface $privateConversationManager, VisitDtoManagerInterface $visitManager,
        FormValidator $formValidator, TokenEncoderInterface $tokenEncoder)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->userManager = $userManager;
        $this->historicAnnouncementManager = $historicAnnouncementManager;
        $this->privateConversationManager = $privateConversationManager;
        $this->visitManager = $visitManager;
        $this->formValidator = $formValidator;
        $this->tokenEncoder = $tokenEncoder;
    }


    /**
     * Gets the authenticated user
     *
     * @Rest\Get(name="rest_get_me")
     *
     * @Operation(tags={ "Me" },
     *   @SWG\Response(response=200, description="User found", @Model(type=UserDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function getSelfAction(Request $request)
    {
        $this->logger->debug("Getting the authenticated user");

        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);

        $this->logger->info("User found", array ("response" => $user));

        return $this->buildJsonResponse($user, Response::HTTP_OK);
    }


    /**
     * Updates the authenticated user
     *
     * @Rest\Put(name="rest_update_me")
     * @Rest\Patch(name="rest_patch_me")
     *
     * @Operation(tags={ "Me" },
     *   @SWG\Parameter(name="user", in="body", required=true, @Model(type=UserDtoForm::class)),
     *   @SWG\Response(response=200, description="User updated", @Model(type=UserDto::class)),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     * @throws InvalidParameterException
     */
    public function updateSelfAction(Request $request)
    {
        $this->logger->debug("Updating the authenticated user", array ("request" => $request->request));

        /** @var User $user */
        $user = $this->userManager->update($this->tokenEncoder->decode($request), $request->request->all(),
            $request->isMethod("PUT"), UserDtoForm::class);

        $this->logger->info("User updated", array ("response" => $user));

        return $this->buildJsonResponse($user, Response::HTTP_OK);
    }


    /**
     * Updates the authenticated user status
     *
     * @Rest\Patch(path="/status", name="rest_patch_me_status")
     *
     * @Operation(tags={ "Me" },
     *   @SWG\Parameter(
     *     name="status", required=true, in="body",
     *     @SWG\Schema(
     *       @SWG\Property(property="value", type="string", description="The status value",
     *         enum={"enabled", "vacation"}, default="enabled"), required={ "value" })),
     *   @SWG\Response(response=200, description="User status updated", @Model(type=UserDto::class)),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidParameterException
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function updateSelfStatusAction(Request $request)
    {
        $this->logger->debug("Changing the status of the authenticated user",
            array ("patchParams" => $request->request->all()));

        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);
        /** @var string $status */
        $status = $request->request->getAlpha("value");

        if ($status != UserStatus::VACATION && $status != UserStatus::ENABLED)
        {
            throw new InvalidParameterException("status", "Invalid status value");
        }

        $user = $this->userManager->updateStatus($user, $status);

        $this->logger->info("User status updated", array ("response" => $user));

        return $this->buildJsonResponse($user, Response::HTTP_OK);
    }


    /**
     * Updates the authenticated user password
     *
     * @Rest\Post(path="/password", name="rest_update_me_password")
     *
     * @Operation(tags={ "Me" },
     *   @SWG\Parameter(name="user", in="body", required=true, @Model(type=EditPasswordForm::class)),
     *   @SWG\Response(response=200, description="User updated"),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function updateSelfPasswordAction(Request $request)
    {
        $this->logger->debug("Updating the password of the authenticated user");

        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);
        $user = $this->userManager->updatePassword($user, $request->request->all());

        $this->logger->info("User password updated", array ("response" => $user));

        return $this->buildJsonResponse($user, Response::HTTP_OK);
    }


    /**
     * Uploads a file as the profile picture the authenticated user
     *
     * @Rest\Post(path="/picture", name="rest_upload_me_picture")
     * @Rest\FileParam(name="file", image=true, nullable=false, description="The picture to upload")
     *
     * @Operation(tags={ "Me" }, consumes={ "multipart/form-data" },
     *   @SWG\Parameter(name="file", in="formData", type="file", required=true, description="The profile picture"),
     *   @SWG\Response(response=200, description="Picture uploaded", @Model(type=ProfilePictureDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No user found"),
     *   @SWG\Response(response=400, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function uploadPictureAction(Request $request)
    {
        $this->logger->debug("Uploading a profile picture for the authenticated user",
            array ("postParams" => $request->files));

        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);
        /** @var ProfilePictureDto $picture */
        $picture = $this->userManager->uploadProfilePicture($user, $request->files->get("file"));

        $this->logger->info("Profile picture uploaded", array ("response" => $picture));

        return $this->buildJsonResponse($picture, Response::HTTP_OK);
    }


    /**
     * Deletes the authenticated user's profile picture
     *
     * @Rest\Delete(path="/picture", name="rest_delete_me_picture")
     *
     * @Operation(tags={ "Me" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(response=204, description="Picture deleted"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No user found")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function deletePictureAction(Request $request)
    {
        $this->logger->debug("Deleting the authenticated user's profile picture");

        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);
        $this->userManager->deleteProfilePicture($user);

        $this->logger->info("Profile picture deleted", array ("user" => $user));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    /**
     * Lists the visits done by the authenticated user
     *
     * @Rest\Get(path="/visits", name="rest_get_me_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters (prefix with '-' to DESC sort)",
     *   default="-createdAt")
     *
     * @Operation(tags={ "Me" },
     *   @SWG\Response(response=200, description="Visits found", @Model(type=VisitPageResponse::class)),
     *   @SWG\Response(response=206, description="Partial content"),
     *   @SWG\Response(response=401, description="Unauthorized")
     * )
     *
     * @param ParamFetcher $fetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getSelfVisitsAction(ParamFetcher $fetcher, Request $request)
    {
        $parameters = $this->extractPageableParameters($fetcher);

        $this->logger->debug("Listing visits done by the authenticated user", $parameters);

        /** @var UserDto $visitor */
        $visitor = $this->tokenEncoder->decode($request);
        $pageable = PageRequest::create($parameters);
        $response = new PageResponse(
            $this->visitManager->listByVisitor($visitor, $pageable), "rest_get_me_visits", $fetcher->all());

        $this->logger->info("Listing visits done by the authenticated user - result information",
            array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Lists the authenticated user's historic announcements
     *
     * @Rest\Get(path="/history/announcements", name="rest_get_me_historic_announcements")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters (prefix with '-' to DESC sort)",
     *   default="-createdAt")
     *
     * @Operation(tags={ "Me" },
     *   @SWG\Response(
     *     response=200, description="Historic announcements found",
     *     @Model(type=HistoricAnnouncementPageResponse::class)),
     *   @SWG\Response(response=206, description="Partial content"),
     *   @SWG\Response(response=401, description="Unauthorized")
     * )
     *
     * @param ParamFetcher $fetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function getSelfHistoricAnnouncementsAction(ParamFetcher $fetcher, Request $request)
    {
        $parameters = $this->extractPageableParameters($fetcher);

        $this->logger->debug("Listing historic announcements of the authenticated user", $parameters);

        /** @var User $user */
        $user = $this->tokenEncoder->decode($request);
        /** @var HistoricAnnouncementFilter $filter */
        $filter = $this->formValidator->validateFilterForm(HistoricAnnouncementFilterForm::class,
            new HistoricAnnouncementFilter(), array ("creatorId" => $user->getId()));
        $pageable = PageRequest::create($parameters);

        $response = new PageResponse($this->historicAnnouncementManager->search($filter, $pageable),
            "rest_get_me_historic_announcements", $fetcher->all());

        $this->logger->info("Listing historic announcements of the authenticated user - result information",
            array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Lists the authenticated user's private conversations
     *
     * @Rest\Get(path="/conversations", name="rest_get_me_private_conversations")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters (prefix with '-' to DESC sort)",
     *   default="-createdAt")
     *
     * @Operation(tags={ "Me" },
     *   @SWG\Response(
     *     response=200, description="Private conversations found",
     *     @Model(type=PrivateConversationPageResponse::class)),
     *   @SWG\Response(response=206, description="Partial content"),
     *   @SWG\Response(response=401, description="Unauthorized")
     * )
     *
     * @param ParamFetcher $fetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getSelfPrivateConversations(ParamFetcher $fetcher, Request $request)
    {
        $parameters = $this->extractPageableParameters($fetcher);

        $this->logger->debug("Listing private conversations of the authenticated user", $parameters);

        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);
        $pageable = PageRequest::create($parameters);

        /** @var PageResponse $response */
        $response = new PageResponse(
            $this->privateConversationManager->findAll($user, $pageable),
            "rest_get_me_private_conversations", $fetcher->all());

        $this->logger->info("Listing private conversations of the authenticated user - result information",
            array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }

}