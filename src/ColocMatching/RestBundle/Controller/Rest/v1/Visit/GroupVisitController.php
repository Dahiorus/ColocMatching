<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Visit\Visit;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\VisitNotFoundException;
use ColocMatching\CoreBundle\Form\Type\Filter\VisitFilterType;
use ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\Visit\GroupVisitControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * REST controller for resources /groups/visits and /groups/{id}/visits
 *
 * @Rest\Route("/groups/{id}/visits")
 *
 * @author Dahiorus
 */
class GroupVisitController extends RestController implements GroupVisitControllerInterface {

    /**
     * Lists the visits on one group with pagination
     *
     * @Rest\Get(path="", name="rest_get_group_visits")
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

        $this->get("logger")->info("Listing visits of one group", array ("groupId" => $id, "pagination" => $pageable));

        /** @var Group $group */
        $group = $this->get("coloc_matching.core.group_manager")->read($id);

        if (!$this->isAuthorized($group)) {
            throw new AccessDeniedException("This user cannot access to the visits");
        }

        /** @var PageableFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($pageable["page"],
            $pageable["size"], $pageable["order"], $pageable["sort"]);
        /** @var VisitManagerInterface $manager */
        $manager = $this->get("coloc_matching.core.group_visit_manager");
        /** @var array<Visit> $visits */
        $visits = $manager->listByVisited($group, $filter);
        /** @var PageResponse $response */
        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($visits,
            $manager->countByVisited($group), $filter);

        $this->get("logger")->info("Listing visits of groups - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Gets an existing visit on an group
     *
     * @Rest\Get(path="/{visitId}", name="rest_get_group_visit")
     *
     * @param int $id
     * @param int $visitId
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function getVisitAction(int $id, int $visitId) {
        $this->get("logger")->info("Getting a visit on groups", array ("id" => $id, "visitId" => $visitId));

        /** @var Group $group */
        $group = $this->get("coloc_matching.core.group_manager")->read($id);

        if (!$this->isAuthorized($group)) {
            throw new AccessDeniedException("This user cannot access to the visit");
        }

        /** @var Visit $visit */
        $visit = $this->get("coloc_matching.core.group_visit_manager")->read($visitId);

        if ($group !== $visit->getVisited()) {
            throw new VisitNotFoundException("id", $visitId);
        }

        $this->get("logger")->info("One visit found", array ("response" => $visit));

        return $this->buildJsonResponse($visit, Response::HTTP_OK);
    }


    /**
     * Searches visits on group(s) by criteria
     *
     * @Rest\Post("/searches", name="rest_search_groups_visits")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchVisitsAction(int $id, Request $request) {
        $this->get("logger")->info("Searching visits on groups", array ("request" => $request));

        /** @var Group $group */
        $group = $this->get("coloc_matching.core.group_manager")->read($id);

        if (!$this->isAuthorized($group)) {
            throw new AccessDeniedException("This user cannot access to the visits");
        }

        /** @var VisitManagerInterface */
        $manager = $this->get("coloc_matching.core.group_visit_manager");

        /** @var VisitFilter $filter */
        $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(VisitFilterType::class,
            new VisitFilter(), $request->request->all());
        $filter->setVisitedId($id);

        /** @var array<Visit> $visits */
        $visits = $manager->search($filter);
        /** @var PageResponse $response */
        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($visits,
            $manager->countBy($filter), $filter);

        $this->get("logger")->info("Searching visits on groups - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    private function isAuthorized(Group $group) {
        /** @var Request $request */
        $request = $this->get("request_stack")->getCurrentRequest();

        return $this->get("coloc_mathing.rest.visit_utils")->isAuthorized($this->extractUser($request), $group);
    }

}