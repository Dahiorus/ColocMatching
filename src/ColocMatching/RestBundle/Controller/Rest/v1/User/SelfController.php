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
use ColocMatching\CoreBundle\Form\Type\Filter\HistoricAnnouncementFilterType;
use ColocMatching\CoreBundle\Form\Type\Filter\VisitFilterType;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Message\PrivateConversationDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Visit\VisitDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\FilterFactory;
use ColocMatching\CoreBundle\Repository\Filter\HistoricAnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\CoreBundle\Security\User\TokenEncoderInterface;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use ColocMatching\RestBundle\Controller\Rest\v1\utils\VisitUtils;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for resources /me
 *
 * @Rest\Route(path="/me", service="coloc_matching.rest.self_controller")
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

    /** @var FilterFactory */
    private $filterBuilder;

    /** @var TokenEncoderInterface */
    private $tokenEncoder;

    /** @var VisitUtils */
    private $visitUtils;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        UserDtoManagerInterface $userManager, HistoricAnnouncementDtoManagerInterface $historicAnnouncementManager,
        PrivateConversationDtoManagerInterface $privateConversationManager, FilterFactory $filterBuilder,
        TokenEncoderInterface $tokenEncoder, VisitUtils $visitUtils)
    {
        parent::__construct($logger, $serializer);

        $this->userManager = $userManager;
        $this->historicAnnouncementManager = $historicAnnouncementManager;
        $this->privateConversationManager = $privateConversationManager;
        $this->filterBuilder = $filterBuilder;
        $this->tokenEncoder = $tokenEncoder;
        $this->visitUtils = $visitUtils;
    }


    /**
     * Gets the authenticated user
     *
     * @Rest\Get(name="rest_get_me")
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function getSelfAction(Request $request)
    {
        $this->logger->info("Getting the authenticated user");

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
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function updateSelfAction(Request $request)
    {
        $this->logger->info("Updating the authenticated user", array ("request" => $request->request));

        return $this->handleUpdateRequest($request, true);
    }


    /**
     * Updates (partial) the authenticated user
     *
     * @Rest\Patch(name="rest_patch_me")
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function patchSelfAction(Request $request)
    {
        $this->logger->info("Patching the authenticated user", array ("request" => $request->request));

        return $this->handleUpdateRequest($request, false);
    }


    /**
     * Updates the status of an existing user
     *
     * @Rest\Patch(path="/status", name="rest_patch_me_status")
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidParameterException
     * @throws EntityNotFoundException
     */
    public function updateSelfStatusAction(Request $request)
    {
        $this->logger->info("Changing the status of the authenticated user",
            array ("request" => $request->request));

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
     * @param Request $request
     *
     * @Rest\Post(path="/password", name="rest_update_me_password")
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function updateSelfPasswordAction(Request $request)
    {
        $this->logger->info("Updating the password of the authenticated user",
            array ("request" => $request->request));

        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);
        $user = $this->userManager->updatePassword($user, $request->request->all());

        $this->logger->info("User password updated", array ("response" => $user));

        return $this->buildJsonResponse($user, Response::HTTP_OK);
    }


    /**
     * Lists the visits done by the authenticated user with pagination
     *
     * @Rest\Get(path="/visits", name="rest_get_me_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="20")
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results",
     *   default="id")
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$",
     *   default="asc")
     * @Rest\QueryParam(name="type", nullable=false, description="The invitable type",
     *   requirements="^(announcement|group|user)$")
     *
     * @param ParamFetcher $fetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function getSelfVisitsAction(ParamFetcher $fetcher, Request $request)
    {
        $filterData = $this->extractPageableParameters($fetcher);
        $visitableType = $fetcher->get("type", true);

        $this->logger->info("Listing visits done by the authenticated user",
            array ("pagination" => $filterData));

        $filterData["visitorId"] = $this->tokenEncoder->decode($request)->getId();
        /** @var VisitFilter $filter */
        $filter = $this->filterBuilder->buildCriteriaFilter(VisitFilterType::class,
            new VisitFilter(), $filterData);

        /** @var VisitDtoManagerInterface $manager */
        $manager = $this->visitUtils->getManager($visitableType);
        /** @var VisitDto[] $visits */
        $visits = $manager->search($filter);
        /** @var PageResponse $response */
        $response = $this->createPageResponse($visits, $manager->countBy($filter), $filter, $request);

        $this->logger->info("Listing visits done by the authenticated user - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Lists historic announcements or specified fields of the authenticated user with pagination
     *
     * @Rest\Get(path="/history/announcements", name="rest_get_me_historic_announcements")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="20")
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results",
     *   default="id")
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$",
     *   default="asc")
     *
     * @param ParamFetcher $fetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function getSelfHistoricAnnouncementsAction(ParamFetcher $fetcher, Request $request)
    {
        $filterData = $this->extractPageableParameters($fetcher);

        $this->logger->info("Listing historic announcements of the authenticated user",
            array ("pagination" => $filterData));

        /** @var User $user */
        $user = $this->tokenEncoder->decode($request);
        $filterData["creatorId"] = $user->getId();

        /** @var HistoricAnnouncementFilter $filter */
        $filter = $this->filterBuilder->buildCriteriaFilter(HistoricAnnouncementFilterType::class,
            new HistoricAnnouncementFilter(), $filterData);

        /** @var HistoricAnnouncementDto[] $announcements */
        $announcements = $this->historicAnnouncementManager->list($filter);
        /** @var PageResponse $response */
        $response = $this->createPageResponse($announcements,
            $this->historicAnnouncementManager->countBy($filter), $filter, $request);

        $this->logger->info("Listing historic announcements of the authenticated user - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Lists private conversations of the authenticated user with pagination
     *
     * @Rest\Get(path="/conversations", name="rest_get_me_private_conversations")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="10")
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results",
     *   default="lastUpdate")
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$",
     *   default="desc")
     *
     * @param ParamFetcher $fetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function getSelfPrivateConversations(ParamFetcher $fetcher, Request $request)
    {
        $filterData = $this->extractPageableParameters($fetcher);

        $this->logger->info("Listing private conversation of the authenticated user",
            array ("pagination" => $filterData));

        /** @var PageableFilter $filter */
        $filter = $this->filterBuilder->createPageableFilter($filterData["page"],
            $filterData["size"], $filterData["order"], $filterData["sort"]);
        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);

        /** @var PageResponse $response */
        $response = $this->createPageResponse(
            $this->privateConversationManager->findAll($user, $filter),
            $this->privateConversationManager->countAll($user), $filter, $request);

        $this->logger->info("Listing private conversations of the authenticated user - result information",
            array ("filter" => $filter, "response" => $response));

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