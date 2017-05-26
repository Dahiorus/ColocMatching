<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\HistoricAnnouncementControllerInterface;
use ColocMatching\CoreBundle\Form\Type\Filter\HistoricAnnouncementFilterType;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\HistoricAnnouncementFilter;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementManager;
use ColocMatching\CoreBundle\Exception\HistoricAnnouncementNotFound;

/**
 * REST controller for the resource /history/announcements
 *
 * @Rest\Route("/history/announcements")
 *
 * @author Dahiorus
 */
class HistoricAnnouncementController extends Controller implements HistoricAnnouncementControllerInterface {


    /**
     * Lists announcements or fields with pagination
     *
     * @Rest\Get("", name="rest_get_historic_announcements")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+", default=RequestConstants::DEFAULT_PAGE)
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+", default=RequestConstants::DEFAULT_LIMIT)
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results", default=RequestConstants::DEFAULT_SORT)
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$", default=RequestConstants::DEFAULT_ORDER)
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return for each result")
     *
     * @param Request $paramFetcher
     * @return JsonResponse
     */
    public function getHistoricAnnouncementsAction(ParamFetcher $paramFetcher) {
        $page = $paramFetcher->get("page", true);
        $limit = $paramFetcher->get("size", true);
        $order = $paramFetcher->get("order", true);
        $sort = $paramFetcher->get("sort", true);
        $fields = $paramFetcher->get("fields");

        $this->get("logger")->info(
            sprintf("Getting historic announcements [page: %d, limit: %d, order: '%s', sort: '%s', fields: [%s]]",
                $page, $limit, $order, $sort, $fields), array ("request" => $paramFetcher));

        /** @var AbstractFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->setFilter(new HistoricAnnouncementFilter(), $page,
            $limit, $order, $sort);
        /** @var HistoricAnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.historic_announcement_manager");
        /** @var array */
        $announcements = empty($fields) ? $manager->list($filter) : $manager->list($filter, explode(",", $fields));
        /** @var RestListResponse */
        $restList = $this->get("coloc_matching.core.rest_response_factory")->createRestListResponse($announcements,
            $manager->countAll(), $filter);

        /** @var int */
        $codeStatus = ($restList->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK;

        $this->get("logger")->info(
            sprintf("Result information : [page: %d, size: %d, total: %d]", $restList->getPage(), $restList->getSize(),
                $restList->getTotalElements()), array ("response" => $restList));

        return new JsonResponse($this->get("jms_serializer")->serialize($restList, "json"), $codeStatus, array (), true);
    }


    /**
     * Gets an existing historic announcement or its fields
     *
     * @Rest\Get("/{id}", name="rest_get_historic_announcement")
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     * @return JsonResponse
     * @throws HistoricAnnouncementNotFound
     */
    public function getAnnouncementAction(int $id, ParamFetcher $paramFetcher) {
        /** @var array */
        $fields = $paramFetcher->get("fields");

        $this->get("logger")->info("Getting an existing historic announcement by id",
            array ("id" => $id, "paramFetcher" => $paramFetcher));

        /** @var HistoricAnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.historic_announcement_manager");
        /** @var Announcement */
        $announcement = (!$fields) ? $manager->read($id) : $manager->read($id, explode(',', $fields));
        /** @var RestDataResponse */
        $restData = $this->get("coloc_matching.core.rest_response_factory")->createRestDataResponse($announcement);

        $this->get("logger")->info("One historic announcement found", array ("id" => $id, "response" => $restData));

        return new JsonResponse($this->get("jms_serializer")->serialize($restData, "json"), Response::HTTP_OK, [ ], true);
    }


    /**
     * Searches historic announcements by criteria
     *
     * @Rest\Post("/searches", name="rest_search_historic_announcements")
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidFormDataException
     */
    public function searchHistoricAnnouncementsAction(Request $request) {
        /** @var array */
        $filterData = $request->request->all();

        $this->get("logger")->info("Searching historic announcements by filter",
            [ "filterData" => $filterData, "request" => $request]);

        /** @var HistoricAnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.historic_announcement_manager");

        try {
            /** @var AnnouncementFilter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(
                HistoricAnnouncementFilterType::class, new HistoricAnnouncementFilter(), $filterData);

            /** @var array */
            $announcements = $manager->search($filter);
            /** @var RestListResponse */
            $restList = $this->get("coloc_matching.core.rest_response_factory")->createRestListResponse($announcements,
                $manager->countBy($filter), $filter);
            /** @var int */
            $codeStatus = ($restList->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK;

            $this->get("logger")->info(
                sprintf("Result information [page: %d, size: %d, total: %d]", $restList->getPage(),
                    $restList->getSize(), $restList->getTotalElements()),
                array ("response" => $restList, "filter" => $filter));

            return new JsonResponse($this->get("jms_serializer")->serialize($restList, "json"), $codeStatus,
                [ "Location" => $request->getUri()], true);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to search historic announcements",
                array ("request" => $request, "exception" => $e));

            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ "Location" => $request->getUri()],
                true);
        }
    }

}