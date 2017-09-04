<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visit;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\VisitNotFoundException;
use ColocMatching\CoreBundle\Form\Type\Filter\VisitFilterType;
use ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\RestBundle\Controller\Response\EntityResponse;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\Visit\AnnouncementVisitControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * REST controller for resources /announcements/visits and /announcements/{id}/visits
 *
 * @Rest\Route("/announcements/{id}/visits")
 * @Security(expression="has_role('ROLE_USER')")
 *
 * @author Dahiorus
 */
class AnnouncementVisitController extends RestController implements AnnouncementVisitControllerInterface {

    /**
     * Lists the visits on one announcement with pagination
     *
     * @Rest\Get(path="", name="rest_get_announcement_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="20")
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results",
     *   default="id")
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$",
     *   default="asc")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getVisitsAction(int $id, ParamFetcher $paramFetcher) {
        $pageable = $this->extractPageableParameters($paramFetcher);

        $this->get("logger")->info("Listing visits of one announcement",
            array ("announcementId" => $id, "pagination" => $pageable));

        /** @var Announcement $announcement */
        $announcement = $this->get("coloc_matching.core.announcement_manager")->read($id);

        if (!$this->isAuthorized($announcement)) {
            throw new AccessDeniedException("This user cannot access to the visits");
        }

        /** @var PageableFilter $filter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($pageable["page"],
            $pageable["size"], $pageable["order"], $pageable["sort"]);
        /** @var VisitManagerInterface $manager */
        $manager = $this->get("coloc_matching.core.announcement_visit_manager");
        /** @var array<Visit> $visits */
        $visits = $manager->listByVisited($announcement, $filter);
        /** @var PageResponse $response */
        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($visits,
            $manager->countByVisited($announcement), $filter);

        $this->get("logger")->info("Listing visits of one announcement - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Gets an existing visit on an announcement
     *
     * @Rest\Get("/{visitId}", name="rest_get_announcement_visit")
     *
     * @param int $id
     * @param int $visitId
     *
     * @return JsonResponse
     */
    public function getVisitAction(int $id, int $visitId) {
        $this->get("logger")->info("Getting a visit on an announcement", array ("id" => $id, "visitId" => $visitId));

        /** @var Announcement $announcement */
        $announcement = $this->get("coloc_matching.core.announcement_manager")->read($id);

        if (!$this->isAuthorized($announcement)) {
            throw new AccessDeniedException("This user cannot access to the visit");
        }

        /** @var Visit $visit */
        $visit = $this->get("coloc_matching.core.announcement_visit_manager")->read($visitId);

        if ($announcement !== $visit->getVisited()) {
            throw new VisitNotFoundException("id", $visitId);
        }

        /** @var EntityResponse $response */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($visit);

        $this->get("logger")->info("One visit found", array ("id" => $id, "response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Searches visits on an announcement by criteria
     *
     * @Rest\Post("/searches", name="rest_search_announcement_visits")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchVisitsAction(int $id, Request $request) {
        $this->get("logger")->info("Searching visits on an announcement", array ("id" => $id, "request" => $request));

        /** @var Announcement $announcement */
        $announcement = $this->get("coloc_matching.core.announcement_manager")->read($id);

        if (!$this->isAuthorized($announcement)) {
            throw new AccessDeniedException("This user cannot access to the visits");
        }

        try {
            /** @var VisitManagerInterface $manager */
            $manager = $this->get("coloc_matching.core.announcement_visit_manager");
            /** @var VisitFilter $filter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(VisitFilterType::class,
                new VisitFilter(), $request->request->all());
            $filter->setVisitedId($id);

            /** @var array<Visit> $visits */
            $visits = $manager->search($filter);
            /** @var PageResponse $response */
            $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($visits,
                $manager->countBy($filter), $filter);

            $this->get("logger")->info("Searching visits on an announcement - result information",
                array ("filter" => $filter, "response" => $response));

            return $this->buildJsonResponse($response,
                ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to search visits on an announcement",
                array ("request" => $request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }


    private function isAuthorized(Announcement $announcement) : bool {
        /** @var Request $request */
        $request = $this->get("request_stack")->getCurrentRequest();
        /** @var User $user */
        $user = $this->extractUser($request);

        return $user === $announcement->getCreator();
    }

}