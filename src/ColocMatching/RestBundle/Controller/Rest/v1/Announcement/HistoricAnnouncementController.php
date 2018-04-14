<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\CommentDto;
use ColocMatching\CoreBundle\DTO\Announcement\HistoricAnnouncementDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Filter\HistoricAnnouncementFilterType;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\FilterFactory;
use ColocMatching\CoreBundle\Repository\Filter\HistoricAnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for the resource /history/announcements
 *
 * @Rest\Route(path="/history/announcements", service="coloc_matching.rest.historic_announcement_controller")
 *
 * @author Dahiorus
 */
class HistoricAnnouncementController extends AbstractRestController
{
    /** @var HistoricAnnouncementDtoManagerInterface */
    private $historicAnnouncementManager;

    /** @var FilterFactory */
    private $filterBuilder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        HistoricAnnouncementDtoManagerInterface $historicAnnouncementManager, FilterFactory $filterBuilder)
    {
        parent::__construct($logger, $serializer);

        $this->historicAnnouncementManager = $historicAnnouncementManager;
        $this->filterBuilder = $filterBuilder;
    }


    /**
     * Lists announcements or fields with pagination
     *
     * @Rest\Get(name="rest_get_historic_announcements")
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
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getHistoricAnnouncementsAction(ParamFetcher $paramFetcher, Request $request)
    {
        $pageable = $this->extractPageableParameters($paramFetcher);

        $this->logger->info("Listing historic announcements", $pageable);

        /** @var PageableFilter $filter */
        $filter = $this->filterBuilder->createPageableFilter($pageable["page"], $pageable["size"], $pageable["order"],
            $pageable["sort"]);
        /** @var HistoricAnnouncementDto[] $announcements */
        $announcements = $this->historicAnnouncementManager->list($filter);
        /** @var PageResponse $response */
        $response = $this->createPageResponse($announcements,
            $this->historicAnnouncementManager->countAll(), $filter, $request);

        $this->logger->info("Listing historic announcements - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Gets an existing historic announcement or its fields
     *
     * @Rest\Get(path="/{id}", name="rest_get_historic_announcement", requirements={"id"="\d+"})
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function getHistoricAnnouncementAction(int $id)
    {
        $this->logger->info("Getting an existing historic announcement", array ("id" => $id));

        /** @var HistoricAnnouncementDto $announcement */
        $announcement = $this->historicAnnouncementManager->read($id);

        $this->logger->info("One historic announcement found", array ("response" => $announcement));

        return $this->buildJsonResponse($announcement, Response::HTTP_OK);
    }


    /**
     * Searches historic announcements by criteria
     *
     * @Rest\Post("/searches", name="rest_search_historic_announcements")
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function searchHistoricAnnouncementsAction(Request $request)
    {
        $this->logger->info("Searching historic announcements by filter",
            array ("postParams" => $request->request->all()));

        /** @var HistoricAnnouncementFilter $filter */
        $filter = $this->filterBuilder->buildCriteriaFilter(HistoricAnnouncementFilterType::class,
            new HistoricAnnouncementFilter(), $request->request->all());
        /** @var HistoricAnnouncementDto[] $announcements */
        $announcements = $this->historicAnnouncementManager->search($filter);
        /** @var PageResponse $response */
        $response = $this->createPageResponse($announcements, $this->historicAnnouncementManager->countBy($filter),
            $filter, $request);

        $this->logger->info("Searching historic announcements by filter - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Gets comments of a historic announcement with pagination
     *
     * @Rest\Get(path="/{id}/comments", name="rest_get_historic_announcement_comments", requirements={"id"="\d+"})
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="10")
     *
     * @param int $id
     * @param ParamFetcher $fetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getCommentsAction(int $id, ParamFetcher $fetcher, Request $request)
    {
        $page = $fetcher->get("page", true);
        $size = $fetcher->get("size", true);

        $this->logger->info("Listing the comments of a historic announcement",
            array ("id" => $id, "pageable" => array ("page" => $page, "size" => $size)));

        /** @var HistoricAnnouncementDto $announcement */
        $announcement = $this->historicAnnouncementManager->read($id);
        /** @var PageableFilter $filter */
        $filter = $this->filterBuilder->createPageableFilter($page, $size);
        /** @var CommentDto[] $comments */
        $comments = $this->historicAnnouncementManager->getComments($announcement, $filter);

        /** @var PageResponse $response */
        $response = $this->createPageResponse($comments,
            $this->historicAnnouncementManager->countComments($announcement), $filter, $request);

        $this->logger->info("Listing the comments of a historic announcement - result information",
            array ("response" => $response));

        return $this->buildJsonResponse($response,
            $response->hasNext() ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }

}
