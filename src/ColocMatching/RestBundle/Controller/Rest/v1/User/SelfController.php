<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\DTO\Announcement\HistoricAnnouncementDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\Visit\VisitDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Form\Type\Filter\HistoricAnnouncementFilterForm;
use ColocMatching\CoreBundle\Form\Type\Security\EditPasswordForm;
use ColocMatching\CoreBundle\Form\Type\User\UserDtoForm;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Message\PrivateConversationDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Visit\VisitDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\HistoricAnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\PageRequest;
use ColocMatching\CoreBundle\Security\User\TokenEncoderInterface;
use ColocMatching\CoreBundle\Validator\FormValidator;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
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
 * @Security(expression="has_role('ROLE_USER')")
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
     */
    public function updateSelfAction(Request $request)
    {
        $this->logger->debug("Updating the authenticated user", array ("request" => $request->request));

        return $this->handleUpdateRequest($request, true);
    }


    /**
     * Updates (partial) the authenticated user
     *
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
     */
    public function patchSelfAction(Request $request)
    {
        $this->logger->debug("Patching the authenticated user", array ("request" => $request->request));

        return $this->handleUpdateRequest($request, false);
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

        if ($status != UserConstants::STATUS_VACATION && $status != UserConstants::STATUS_ENABLED)
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
     * @param Request $request
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
     * Lists the visits done by the authenticated user
     *
     * @Rest\Get(path="/visits", name="rest_get_me_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters", default="createdAt")
     *
     * @Operation(tags={ "Me" },
     *   @SWG\Response(response=200, description="Visits found"),
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
        /** @var VisitDto[] $visits */
        $visits = $this->visitManager->listByVisitor($visitor, $pageable);
        $response = new PageResponse($visits, "rest_get_me_visits", $fetcher->all(), $pageable,
            $this->visitManager->countByVisitor($visitor));

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
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters", default="createdAt")
     *
     * @Operation(tags={ "Me" },
     *   @SWG\Response(response=200, description="Historic announcement found"),
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

        /** @var HistoricAnnouncementDto[] $announcements */
        $announcements = $this->historicAnnouncementManager->search($filter, $pageable);
        $response = new PageResponse($announcements, "rest_get_me_historic_announcements",
            $fetcher->all(), $pageable, $this->historicAnnouncementManager->countBy($filter));

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
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters", default="createdAt")
     *
     * @Operation(tags={ "Me" },
     *   @SWG\Response(response=200, description="Private conversations found"),
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
            "rest_get_me_private_conversations", $fetcher->all(),
            $pageable, $this->privateConversationManager->countAll($user));

        $this->logger->info("Listing private conversations of the authenticated user - result information",
            array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Handles update request
     *
     * @param Request $request The request
     * @param bool $fullUpdate If the operation is a PATCH or a PUT
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    private function handleUpdateRequest(Request $request, bool $fullUpdate)
    {
        /** @var User $user */
        $user = $this->userManager->update($this->tokenEncoder->decode($request), $request->request->all(),
            $fullUpdate);

        $this->logger->info("User updated", array ("response" => $user));

        return $this->buildJsonResponse($user, Response::HTTP_OK);
    }

}