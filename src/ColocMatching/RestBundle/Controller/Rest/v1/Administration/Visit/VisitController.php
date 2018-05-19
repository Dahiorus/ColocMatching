<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Administration\Visit;

use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Filter\VisitFilterForm;
use ColocMatching\CoreBundle\Manager\Visit\VisitDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\PageRequest;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
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
 * Visits administration controller
 *
 * @author Dahiorus
 *
 * @Rest\Route(path="/visits", service="coloc_matching.rest.admin.visit_controller")
 */
class VisitController extends AbstractRestController
{
    /** @var VisitDtoManagerInterface */
    private $visitManager;

    /** @var FormValidator */
    private $formValidator;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, VisitDtoManagerInterface $visitManager,
        FormValidator $formValidator)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->visitManager = $visitManager;
        $this->formValidator = $formValidator;
    }


    /**
     * Lists visits
     *
     * @Rest\Get(name="rest_admin_get_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", map=true, description="Sorting parameters", requirements="\w+,(asc|desc)",
     *   default={ "createdAt,asc" }, allowBlank=false)
     *
     * @Operation(tags={ "Visits" },
     *   @SWG\Response(response=200, description="Visits found"),
     *   @SWG\Response(response=206, description="Partial content"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Forbidden access")
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getVisitsAction(ParamFetcher $paramFetcher)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->debug("Listing visits", $parameters);

        $pageable = PageRequest::create($parameters);
        $response = new PageResponse(
            $this->visitManager->list($pageable),
            "rest_admin_get_visits", $paramFetcher->all(),
            $pageable, $this->visitManager->countAll());

        $this->logger->info("Listing visits - result information",
            array ("pageable" => $pageable, "response" => $response));

        return $this->buildJsonResponse($response, ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT :
            Response::HTTP_OK);
    }


    /**
     * Searches specific visits
     *
     * @Rest\Post(path="/searches", name="rest_admin_search_visits")
     *
     * @Operation(tags={ "Visits" },
     *   @SWG\Parameter(name="filter", in="body", required=true, description="Criteria filter",
     *     @Model(type=VisitFilterForm::class)),
     *   @SWG\Response(response=200, description="Visits found"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Forbidden access")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function searchVisitsAction(Request $request)
    {
        $this->logger->debug("Searching specific visits", array ("postParams" => $request->request->all()));

        /** @var VisitFilter $filter */
        $filter = $this->formValidator->validateFilterForm(VisitFilterForm::class, new VisitFilter(),
            $request->request->all());
        $response = new CollectionResponse(
            $this->visitManager->search($filter, $filter->getPageable()), "rest_admin_search_visits");

        $this->logger->info("Searching visits - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response);
    }

}
