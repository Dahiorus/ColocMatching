<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\Controller\Response\PageResponse;
use ColocMatching\CoreBundle\Controller\Rest\RestController;
use ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\Visit\AnnouncementVisitControllerInterface;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Filter\VisitFilterType;
use ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for resources /announcements/visits and /announcements/{id}/visits
 *
 * @Rest\Route("/announcements")
 *
 * @author Dahiorus
 */
class AnnouncementVisitController extends RestController implements AnnouncementVisitControllerInterface {

    /**
     * Lists the visits on announcements with pagination
     *
     * @Rest\Get("/visits", name="rest_get_announcements_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="20")
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results",
     *   default="id")
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$",
     *   default="asc")
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getVisitsAction(ParamFetcher $paramFetcher) {
        $pageable = $this->extractPageableParameters($paramFetcher);

        $this->get("logger")->info("Listing visits of announcements", array ("pagination" => $pageable));

        /** @var PageableFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($pageable["page"],
            $pageable["limit"], $pageable["order"], $pageable["sort"]);
        /** @var VisitManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_visit_manager");
        /** @var array */
        $visits = $manager->list($filter);
        /** @var PageResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($visits, $manager->countAll(), $filter);

        $this->get("logger")->info("Listing visits of announcements - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response, ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Lists the visits on one announcement with pagination
     *
     * @Rest\Get("/{id}/visits", name="rest_get_announcement_visits")
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
    public function getAnnouncementVisitsAction(int $id, ParamFetcher $paramFetcher) {
        $pageable = $this->extractPageableParameters($paramFetcher);

        $this->get("logger")->info("Listing visits of one announcement", array ("announcement Id" => $id, "pagination" => $pageable));

        /** @var PageableFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($pageable["page"],
            $pageable["limit"], $pageable["order"], $pageable["sort"]);
        /** @var VisitManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_visit_manager");
        /** @var array */
        $visits = $manager->listByVisited($this->get("coloc_matching.core.announcement_manager")->read($id), $filter);
        /** @var PageResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($visits, $manager->countAll(), $filter);

        $this->get("logger")->info("Listing visits of announcements - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response, ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Gets an existing visit on an announcement
     *
     * @Rest\Get("/visits/{id}", name="rest_get_announcement_visit")
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getAnnouncementVisitAction(int $id) {
        $this->get("logger")->info("Getting a visit on announcements", array ("id" => $id));

        $visit = $this->get("coloc_matching.core.announcement_visit_manager")->read($id);
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($visit);

        $this->get("logger")->info("One visit found", array ("id" => $id, "response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Searches visits on announcement(s) by criteria
     *
     * @Rest\Post("/visits/searches", name="rest_search_announcements_visits")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchVisitsAction(Request $request) {
        $this->get("logger")->info("Searching visits on announcements", array ("request" => $request));

        /** @var VisitManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_visit_manager");

        try {
            /** @var VisitFilter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(VisitFilterType::class,
                new VisitFilter(), $request->request->all());
            /** @var array<Visit> */
            $visits = $manager->search($filter);
            /** @var PageResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($visits,
                $manager->countBy($filter), $filter);

            $this->get("logger")->info("Searching visits on announcements - result information",
                array ("filter" => $filter, "response" => $response));

            return $this->buildJsonResponse($response, ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
        } catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to search visits on announcements",
                array ("request" => $request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }

}