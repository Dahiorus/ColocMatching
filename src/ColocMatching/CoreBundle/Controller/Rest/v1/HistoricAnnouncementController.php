<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Controller\Response\EntityResponse;
use ColocMatching\CoreBundle\Controller\Response\PageResponse;
use ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\HistoricAnnouncementControllerInterface;
use ColocMatching\CoreBundle\Controller\Rest\RestController;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Exception\HistoricAnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Filter\HistoricAnnouncementFilterType;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\HistoricAnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for the resource /history/announcements
 *
 * @Rest\Route("/history/announcements")
 *
 * @author Dahiorus
 */
class HistoricAnnouncementController extends RestController implements HistoricAnnouncementControllerInterface {


    /**
     * Lists announcements or fields with pagination
     *
     * @Rest\Get("", name="rest_get_historic_announcements")
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
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getHistoricAnnouncementsAction(ParamFetcher $paramFetcher) {
        $page = $paramFetcher->get("page", true);
        $limit = $paramFetcher->get("size", true);
        $order = $paramFetcher->get("order", true);
        $sort = $paramFetcher->get("sort", true);
        $fields = $paramFetcher->get("fields");

        $this->get("logger")->info("Listing historic announcements",
            array ("page" => $page, "size" => $limit, "order" => $order, "sort" => $sort, "fields" => $fields));

        /** @var PageableFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($page, $limit, $order, $sort);
        /** @var HistoricAnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.historic_announcement_manager");
        /** @var array */
        $announcements = empty($fields) ? $manager->list($filter) : $manager->list($filter, explode(",", $fields));
        /** @var PageResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($announcements,
            $manager->countAll(), $filter);

        $this->get("logger")->info("Listing historic announcements - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Gets an existing historic announcement or its fields
     *
     * @Rest\Get("/{id}", name="rest_get_historic_announcement")
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws HistoricAnnouncementNotFoundException
     */
    public function getHistoricAnnouncementAction(int $id, ParamFetcher $paramFetcher) {
        /** @var array */
        $fields = $paramFetcher->get("fields");

        $this->get("logger")->info("Getting an existing historic announcement",
            array ("id" => $id, "fileds" => $fields));

        /** @var HistoricAnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.historic_announcement_manager");
        /** @var Announcement */
        $announcement = (!$fields) ? $manager->read($id) : $manager->read($id, explode(',', $fields));
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($announcement);

        $this->get("logger")->info("One historic announcement found", array ("id" => $id, "response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Searches historic announcements by criteria
     *
     * @Rest\Post("/searches", name="rest_search_historic_announcements")
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormDataException
     */
    public function searchHistoricAnnouncementsAction(Request $request) {
        $this->get("logger")->info("Searching historic announcements by filter", array ("request" => $request));

        /** @var HistoricAnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.historic_announcement_manager");

        try {
            /** @var AnnouncementFilter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(
                HistoricAnnouncementFilterType::class, new HistoricAnnouncementFilter(), $request->request->all());

            /** @var array */
            $announcements = $manager->search($filter);
            /** @var PageResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($announcements,
                $manager->countBy($filter), $filter);

            $this->get("logger")->info("Searching historic announcements by filter - result information",
                array ("filter" => $filter, "response" => $response));

            return $this->buildJsonResponse($response,
                ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
        } catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to search historic announcements",
                array ("request" => $request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }

}