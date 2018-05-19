<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Administration\Announcement;

use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Filter\HistoricAnnouncementFilterForm;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\HistoricAnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\PageRequest;
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
 * @Rest\Route(path="/history/announcements", service="coloc_matching.rest.admin.historic_announcement_controller")
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
     * @Rest\Get(name="rest_admin_get_historic_announcements")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters", default="createdAt")
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

        $this->logger->debug("Listing historic announcements", $parameters);

        $pageable = PageRequest::create($parameters);
        $response = new PageResponse(
            $this->historicAnnouncementManager->list($pageable),
            "rest_admin_get_historic_announcements", $paramFetcher->all(),
            $pageable, $this->historicAnnouncementManager->countAll());

        $this->logger->info("Listing historic announcements - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Searches specific historic announcements
     *
     * @Rest\Post("/searches", name="rest_admin_search_historic_announcements")
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
        $response = new CollectionResponse(
            $this->historicAnnouncementManager->search($filter, $filter->getPageable()),
            "rest_admin_search_historic_announcements");

        $this->logger->info("Searching historic announcements - result information", array ("response" => $response));

        return $this->buildJsonResponse($response);
    }

}
