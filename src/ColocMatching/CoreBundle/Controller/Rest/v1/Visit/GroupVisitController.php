<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\Controller\Response\PageResponse;
use ColocMatching\CoreBundle\Controller\Rest\RestController;
use ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\Visit\GroupVisitControllerInterface;
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
 * REST controller for resources /groups/visits and /groups/{id}/visits
 *
 * @Rest\Route("/groups")
 *
 * @author Dahiorus
 */
class GroupVisitController extends RestController implements GroupVisitControllerInterface {

    /**
     * Lists the visits on groups with pagination
     *
     * @Rest\Get("/visits", name="rest_get_groups_visits")
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

        $this->get("logger")->info("Listing visits of groups", array ("pagination" => $pageable));

        /** @var PageableFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($pageable["page"],
            $pageable["size"], $pageable["order"], $pageable["sort"]);
        /** @var VisitManagerInterface */
        $manager = $this->get("coloc_matching.core.group_visit_manager");
        /** @var array */
        $visits = $manager->list($filter);
        /** @var PageResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($visits,
            $manager->countAll(), $filter);

        $this->get("logger")->info("Listing visits of groups - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Lists the visits on one group with pagination
     *
     * @Rest\Get("/{id}/visits", name="rest_get_group_visits")
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
    public function getGroupVisitsAction(int $id, ParamFetcher $paramFetcher) {
        $pageable = $this->extractPageableParameters($paramFetcher);

        $this->get("logger")->info("Listing visits of one group", array ("group Id" => $id, "pagination" => $pageable));

        /** @var PageableFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($pageable["page"],
            $pageable["size"], $pageable["order"], $pageable["sort"]);
        /** @var VisitManagerInterface */
        $manager = $this->get("coloc_matching.core.group_visit_manager");
        /** @var array */
        $visits = $manager->listByVisited($this->get("coloc_matching.core.group_manager")->read($id), $filter);
        /** @var PageResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($visits,
            $manager->countAll(), $filter);

        $this->get("logger")->info("Listing visits of groups - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Gets an existing visit on an group
     *
     * @Rest\Get("/visits/{id}", name="rest_get_group_visit")
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getGroupVisitAction(int $id) {
        $this->get("logger")->info("Getting a visit on groups", array ("id" => $id));

        $visit = $this->get("coloc_matching.core.group_visit_manager")->read($id);
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($visit);

        $this->get("logger")->info("One visit found", array ("id" => $id, "response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Searches visits on group(s) by criteria
     *
     * @Rest\Post("/visits/searches", name="rest_search_groups_visits")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchVisitsAction(Request $request) {
        $this->get("logger")->info("Searching visits on groups", array ("request" => $request));

        /** @var VisitManagerInterface */
        $manager = $this->get("coloc_matching.core.group_visit_manager");

        try {
            /** @var VisitFilter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(VisitFilterType::class,
                new VisitFilter(), $request->request->all());
            /** @var array<Visit> */
            $visits = $manager->search($filter);
            /** @var PageResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($visits,
                $manager->countBy($filter), $filter);

            $this->get("logger")->info("Searching visits on groups - result information",
                array ("filter" => $filter, "response" => $response));

            return $this->buildJsonResponse($response,
                ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to search visits on groups",
                array ("request" => $request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }

}