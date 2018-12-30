<?php

namespace App\Rest\Controller\v1\Administration\Announcement;

use App\Core\Exception\InvalidFormException;
use App\Core\Exception\UnsupportedSerializationException;
use App\Core\Form\Type\Filter\HistoricAnnouncementFilterForm;
use App\Core\Manager\Announcement\HistoricAnnouncementDtoManagerInterface;
use App\Core\Repository\Filter\Converter\StringConverterInterface;
use App\Core\Repository\Filter\HistoricAnnouncementFilter;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Validator\FormValidator;
use App\Rest\Controller\Response\Announcement\HistoricAnnouncementCollectionResponse;
use App\Rest\Controller\Response\Announcement\HistoricAnnouncementPageResponse;
use App\Rest\Controller\Response\CollectionResponse;
use App\Rest\Controller\Response\PageResponse;
use App\Rest\Controller\v1\AbstractRestController;
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for the resource /history/announcements
 *
 * @Rest\Route(path="/history/announcements")
 *
 * @author Dahiorus
 */
class AdminHistoricAnnouncementController extends AbstractRestController
{
    /** @var HistoricAnnouncementDtoManagerInterface */
    private $historicAnnouncementManager;

    /** @var FormValidator */
    private $formValidator;

    /** @var RouterInterface */
    private $router;

    /** @var StringConverterInterface */
    private $stringConverter;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker,
        HistoricAnnouncementDtoManagerInterface $historicAnnouncementManager, FormValidator $formValidator,
        RouterInterface $router, StringConverterInterface $stringConverter)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->historicAnnouncementManager = $historicAnnouncementManager;
        $this->formValidator = $formValidator;
        $this->router = $router;
        $this->stringConverter = $stringConverter;
    }


    /**
     * Lists historic announcements
     *
     * @Rest\Get(name="rest_admin_get_historic_announcements")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters (prefix with '-' to DESC sort)",
     *   default="-createdAt")
     *
     * @Operation(tags={ "Announcement - history" },
     *   @SWG\Response(
     *     response=200, description="Historic announcements found",
     *     @Model(type=HistoricAnnouncementPageResponse::class))
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

        $this->logger->debug("Listing historic announcements", $parameters);

        $pageable = PageRequest::create($parameters);
        $response = new PageResponse(
            $this->historicAnnouncementManager->list($pageable),
            "rest_admin_get_historic_announcements", $paramFetcher->all());

        $this->logger->info("Listing historic announcements - result information", array ("response" => $response));

        return $this->buildJsonResponse($response);
    }


    /**
     * Searches specific historic announcements
     *
     * @Rest\Post(path="/searches", name="rest_admin_search_historic_announcements")
     *
     * @Operation(tags={ "Announcement - history" },
     *   @SWG\Parameter(name="filter", in="body", required=true, description="Criteria filter",
     *     @Model(type=HistoricAnnouncementFilterForm::class)),
     *   @SWG\Response(
     *     response=201, description="Historic announcements found",
     *     @Model(type=HistoricAnnouncementCollectionResponse::class)),
     *   @SWG\Response(response=400, description="Validation error"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function searchHistoricAnnouncementsAction(Request $request)
    {
        $this->logger->debug("Searching specific historic announcements",
            array ("postParams" => $request->request->all()));

        /** @var HistoricAnnouncementFilter $filter */
        $filter = $this->formValidator->validateFilterForm(HistoricAnnouncementFilterForm::class,
            new HistoricAnnouncementFilter(), $request->request->all());
        $convertedFilter = $this->stringConverter->toString($filter);

        $response = new CollectionResponse(
            $this->historicAnnouncementManager->search($filter, $filter->getPageable()),
            "rest_admin_get_searched_historic_announcements", ["filter" => $convertedFilter]);

        $this->logger->info("Searching historic announcements - result information", array ("response" => $response));

        $location = $this->router->generate("rest_admin_get_searched_historic_announcements",
            array ("filter" => $convertedFilter), Router::ABSOLUTE_URL);

        return $this->buildJsonResponse($response, Response::HTTP_CREATED, array ("Location" => $location));
    }


    /**
     * Gets searched historic announcements from the base 64 JSON string filter
     *
     * @Rest\Get(path="/searches/{filter}", name="rest_admin_get_searched_historic_announcements")
     *
     * @Operation(tags={ "Announcement - history" },
     *   @SWG\Parameter(
     *     name="filter", in="path", type="string", required=true, description="Base 64 JSON string filter"),
     *   @SWG\Response(
     *     response=200, description="Historic announcements found",
     *     @Model(type=HistoricAnnouncementCollectionResponse::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="Unsupported base64 string conversion")
     * )
     *
     * @param string $filter
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getSearchedHistoricAnnouncementsAction(string $filter)
    {
        $this->logger->debug("Getting searched historic announcements from a base 64 string filter",
            array ("filter" => $filter));

        try
        {
            /** @var HistoricAnnouncementFilter $announcementFilter */
            $announcementFilter = $this->stringConverter->toObject($filter, HistoricAnnouncementFilter::class);
        }
        catch (UnsupportedSerializationException $e)
        {
            throw new NotFoundHttpException("No filter found with the given base64 string", $e);
        }

        $response = new CollectionResponse(
            $this->historicAnnouncementManager->search($announcementFilter, $announcementFilter->getPageable()),
            "rest_admin_get_searched_historic_announcements", array ("filter" => $filter));

        $this->logger->info("Searching historic announcements by filtering - result information",
            array ("filter" => $announcementFilter, "response" => $response));

        return $this->buildJsonResponse($response);
    }

}
