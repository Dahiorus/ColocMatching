<?php

namespace App\Rest\Controller\v1\Administration\Announcement;

use App\Core\Manager\Announcement\HistoricAnnouncementDtoManagerInterface;
use App\Core\Repository\Filter\Converter\StringConverterInterface;
use App\Core\Repository\Filter\HistoricAnnouncementFilter;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Rest\Controller\Response\Announcement\HistoricAnnouncementPageResponse;
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

    /** @var StringConverterInterface */
    private $stringConverter;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker,
        HistoricAnnouncementDtoManagerInterface $historicAnnouncementManager, StringConverterInterface $stringConverter)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->historicAnnouncementManager = $historicAnnouncementManager;
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
     * @Rest\QueryParam(
     *   name="q", nullable=true,
     *   description=
     *     "Search query to filter results (csv), parameters are in the form 'name:value'")
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
        $filter = $this->extractQueryFilter(HistoricAnnouncementFilter::class, $paramFetcher, $this->stringConverter);
        $pageable = PageRequest::create($parameters);

        $this->logger->debug("Listing historic announcements", array_merge($parameters, ["filter" => $filter]));

        $result = empty($filter) ? $this->historicAnnouncementManager->list($pageable)
            : $this->historicAnnouncementManager->search($filter, $pageable);
        $response = new PageResponse($result, "rest_admin_get_historic_announcements", $paramFetcher->all());

        $this->logger->info("Listing historic announcements - result information", array ("response" => $response));

        return $this->buildJsonResponse($response);
    }

}
