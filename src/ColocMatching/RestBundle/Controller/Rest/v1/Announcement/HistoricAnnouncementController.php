<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\HistoricAnnouncementDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Filter\HistoricAnnouncementFilterForm;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\HistoricAnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\Order;
use ColocMatching\CoreBundle\Repository\Filter\PageRequest;
use ColocMatching\CoreBundle\Validator\FormValidator;
use ColocMatching\RestBundle\Controller\Response\CollectionResponse;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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

    /** @var FormValidator */
    private $formValidator;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker,
        HistoricAnnouncementDtoManagerInterface $historicAnnouncementManager, FormValidator $formValidator)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->historicAnnouncementManager = $historicAnnouncementManager;
        $this->formValidator = $formValidator;
    }


    /**
     * Lists historic announcements
     *
     * @Rest\Get(name="rest_get_historic_announcements")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", map=true, description="Sorting parameters", requirements="\w+,(asc|desc)",
     *   default={ "createdAt,asc" }, allowBlank=false)
     *
     * @Operation(tags={ "Announcement - history" },
     *   @SWG\Response(response=200, description="Historic announcements found"),
     *   @SWG\Response(response=206, description="Partial content"),
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getHistoricAnnouncementsAction(ParamFetcher $paramFetcher)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->info("Listing historic announcements", $parameters);

        $pageable = PageRequest::create($parameters);
        $response = new PageResponse(
            $this->historicAnnouncementManager->list($pageable),
            "rest_get_historic_announcements", $paramFetcher->all(),
            $pageable, $this->historicAnnouncementManager->countAll());

        $this->logger->info("Listing historic announcements - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Gets an existing historic announcement
     *
     * @Rest\Get(path="/{id}", name="rest_get_historic_announcement", requirements={"id"="\d+"})
     *
     * @Operation(tags={ "Announcement - history" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Response(
     *     response=200, description="Historic announcement found", @Model(type=HistoricAnnouncementDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No historic announcement found"),
     * )
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
     * Searches specific historic announcements
     *
     * @Rest\Post("/searches", name="rest_search_historic_announcements")
     * @Rest\RequestParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\RequestParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\RequestParam(name="sorts", map=true, description="Sorting parameters", requirements="\w+,(asc|desc)",
     *   default={ "createdAt,asc" }, allowBlank=false)
     *
     * @Operation(tags={ "Announcement - history" },
     *   @SWG\Parameter(name="filter", in="body", required=true, description="Criteria filter",
     *     @Model(type=HistoricAnnouncementFilterForm::class)),
     *   @SWG\Response(response=200, description="Historic announcements found"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function searchHistoricAnnouncementsAction(ParamFetcher $paramFetcher, Request $request)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->info("Searching specific  historic announcements",
            array_merge(array ("postParams" => $request->request->all()), $parameters));

        $filter = $this->formValidator->validateFilterForm(HistoricAnnouncementFilterForm::class,
            new HistoricAnnouncementFilter(), $request->request->all());
        $pageable = PageRequest::create($parameters);
        $response = new CollectionResponse(
            $this->historicAnnouncementManager->search($filter, $pageable), "rest_search_historic_announcements");

        $this->logger->info("Searching historic announcements - result information", array ("response" => $response));

        return $this->buildJsonResponse($response);
    }


    /**
     * Gets comments of a historic announcement
     *
     * @Rest\Get(path="/{id}/comments", name="rest_get_historic_announcement_comments", requirements={"id"="\d+"})
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="10")
     *
     * @Operation(tags={ "Announcement - history" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(name="filter", in="body", required=true, description="Criteria filter",
     *     @Model(type=HistoricAnnouncementFilterForm::class)),
     *   @SWG\Response(response=200, description="Historic announcement comments found"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=206, description="Partial content"),
     * )
     *
     * @param int $id
     * @param ParamFetcher $fetcher
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getCommentsAction(int $id, ParamFetcher $fetcher)
    {
        $page = $fetcher->get("page", true);
        $size = $fetcher->get("size", true);

        $this->logger->info("Listing a historic announcement comments",
            array ("id" => $id, "page" => $page, "size" => $size));

        /** @var HistoricAnnouncementDto $announcement */
        $announcement = $this->historicAnnouncementManager->read($id);
        $pageable = new PageRequest($page, $size, array ("createdAt" => Order::DESC));
        $response = new PageResponse(
            $this->historicAnnouncementManager->getComments($announcement, $pageable),
            "rest_get_historic_announcement_comments", array ("id" => $id, "page" => $page, "size" => $size),
            $pageable, $this->historicAnnouncementManager->countComments($announcement));

        $this->logger->info("Listing a historic announcement comments - result information",
            array ("response" => $response));

        return $this->buildJsonResponse($response,
            $response->hasNext() ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }

}
