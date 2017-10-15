<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Filter\HistoricAnnouncementFilterType;
use ColocMatching\CoreBundle\Form\Type\Filter\VisitFilterType;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementManagerInterface;
use ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\HistoricAnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\RestBundle\Controller\Response\EntityResponse;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\User\SelfControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * REST controller for resources /me
 *
 * @Rest\Route("/me")
 * @Security(expression="has_role('ROLE_USER')")
 *
 * @author Dahiorus
 */
class SelfController extends RestController implements SelfControllerInterface {

    private const USER = "user";
    private const ANNOUNCEMENT = "announcement";
    private const GROUP = "group";


    /**
     * Gets the authenticated user
     *
     * @Rest\Get(path="", name="rest_get_me")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getSelfAction(Request $request) {
        $this->get("logger")->info("Getting the authenticated user");

        /** @var User $user */
        $user = $this->extractUser($request);
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($user);

        $this->get("logger")->info("User found", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Updates the authenticated user
     *
     * @Rest\Put(path="", name="rest_update_me")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateSelfAction(Request $request) {
        $this->get("logger")->info("Updating the authenticated user", array ("request" => $request->request));

        return $this->handleUpdateRequest($request, true);
    }


    /**
     * Updates (partial) the authenticated user
     *
     * @Rest\Patch(path="", name="rest_patch_me")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function patchSelfAction(Request $request) {
        $this->get("logger")->info("Patching the authenticated user", array ("request" => $request->request));

        return $this->handleUpdateRequest($request, false);
    }


    /**
     * Updates the status of an existing user
     *
     * @Rest\Patch("/status", name="rest_patch_me_status")
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     */
    public function updateSelfStatusAction(Request $request) {
        $this->get("logger")->info("Changing the status of the authenticated user",
            array ("request" => $request->request));

        /** @var User $user */
        $user = $this->extractUser($request);
        /** @var string $status */
        $status = $request->request->getAlpha("value");

        if ($status != UserConstants::STATUS_VACATION && $status != UserConstants::STATUS_ENABLED) {
            throw new BadRequestHttpException("Unknown status value '$status'");
        }

        /** @var EntityResponse $response */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse(
            $this->get("coloc_matching.core.user_manager")->updateStatus($user, $status));

        $this->get("logger")->info("User status updated", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
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
     *
     * @return JsonResponse
     */
    public function getSelfVisitsAction(ParamFetcher $fetcher) {
        $filterData = $this->extractPageableParameters($fetcher);
        $visitableType = $fetcher->get("type", true);

        $this->get("logger")->info("Listing visits done by the authenticated user",
            array ("pagination" => $filterData));

        $filterData["visitorId"] = $this->extractUser()->getId();
        /** @var VisitFilter $filter */
        $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(VisitFilterType::class,
            new VisitFilter(), $filterData);

        /** @var VisitManagerInterface $manager */
        $manager = $this->getManager($visitableType);
        /** @var array<Visit> $visits */
        $visits = $manager->search($filter);
        /** @var PageResponse $response */
        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($visits,
            $manager->countBy($filter), $filter);

        $this->get("logger")->info("Listing visits done by the authenticated user - result information",
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
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return for each result")
     *
     * @param ParamFetcher $fetcher
     *
     * @return JsonResponse
     */
    public function getSelfHistoricAnnouncementsAction(ParamFetcher $fetcher) {
        $filterData = $this->extractPageableParameters($fetcher);
        $fields = $fetcher->get("fields");

        $this->get("logger")->info("Listing historic announcements of the authenticated user",
            array ("pagination" => $filterData));

        /** @var User $user */
        $user = $this->extractUser($this->get("request_stack")->getCurrentRequest());

        $filterData["creatorId"] = $user->getId();

        /** @var HistoricAnnouncementFilter $filter */
        $filter = $this->get("coloc_matching.core.filter_factory")
            ->buildCriteriaFilter(HistoricAnnouncementFilterType::class, new HistoricAnnouncementFilter(), $filterData);

        /** @var HistoricAnnouncementManagerInterface $manager */
        $manager = $this->get("coloc_matching.core.historic_announcement_manager");
        /** @var array<HistoricAnnouncement> $announcements */
        $announcements = empty($fields) ? $manager->search($filter) : $manager->search($filter, implode(",", $fields));
        /** @var PageResponse $response */
        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($announcements,
            $manager->countBy($filter), $filter);

        $this->get("logger")->info("Listing historic announcements of the authenticated user - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    private function handleUpdateRequest(Request $request, bool $fullUpdate) {
        /** @var User $user */
        $user = $this->get("coloc_matching.core.user_manager")->update($this->extractUser($request),
            $request->request->all(), $fullUpdate);
        /** @var EntityResponse $response */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($user);

        $this->get("logger")->info("User updated", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    private function getManager(string $visitableType) : VisitManagerInterface {
        $manager = null;

        switch ($visitableType) {
            case self::USER:
                $manager = $this->get("coloc_matching.core.user_visit_manager");
                break;
            case self::ANNOUNCEMENT:
                $manager = $this->get("coloc_matching.core.announcement_visit_manager");
                break;
            case self::GROUP:
                $manager = $this->get("coloc_matching.core.group_visit_manager");
                break;
            default:
                throw new \Exception("Unknown visitable type");
                break;
        }

        return $manager;
    }

}