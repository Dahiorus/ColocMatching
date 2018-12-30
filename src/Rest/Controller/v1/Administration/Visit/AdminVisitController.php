<?php

namespace App\Rest\Controller\v1\Administration\Visit;

use App\Core\Exception\InvalidFormException;
use App\Core\Exception\UnsupportedSerializationException;
use App\Core\Form\Type\Filter\VisitFilterForm;
use App\Core\Manager\Visit\VisitDtoManagerInterface;
use App\Core\Repository\Filter\Converter\StringConverterInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Repository\Filter\VisitFilter;
use App\Core\Validator\FormValidator;
use App\Rest\Controller\Response\CollectionResponse;
use App\Rest\Controller\Response\PageResponse;
use App\Rest\Controller\Response\Visit\VisitCollectionResponse;
use App\Rest\Controller\Response\Visit\VisitPageResponse;
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
 * Visits administration controller
 *
 * @author Dahiorus
 *
 * @Rest\Route(path="/visits")
 */
class AdminVisitController extends AbstractRestController
{
    /** @var VisitDtoManagerInterface */
    private $visitManager;

    /** @var FormValidator */
    private $formValidator;

    /** @var RouterInterface */
    private $router;

    /** @var StringConverterInterface */
    private $stringConverter;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, VisitDtoManagerInterface $visitManager,
        FormValidator $formValidator, RouterInterface $router, StringConverterInterface $stringConverter)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->visitManager = $visitManager;
        $this->formValidator = $formValidator;
        $this->router = $router;
        $this->stringConverter = $stringConverter;
    }


    /**
     * Lists visits
     *
     * @Rest\Get(name="rest_admin_get_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters (prefix with '-' to DESC sort)",
     *   default="-createdAt")
     *
     * @Operation(tags={ "Visits" },
     *   @SWG\Response(response=200, description="Visits found", @Model(type=VisitPageResponse::class)),
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
        $response = new PageResponse($this->visitManager->list($pageable),
            "rest_admin_get_visits", $paramFetcher->all());

        $this->logger->info("Listing visits - result information",
            array ("pageable" => $pageable, "response" => $response));

        return $this->buildJsonResponse($response);
    }


    /**
     * Searches specific visits
     *
     * @Rest\Post(path="/searches", name="rest_admin_search_visits")
     *
     * @Operation(tags={ "Visits" },
     *   @SWG\Parameter(name="filter", in="body", required=true, description="Criteria filter",
     *     @Model(type=VisitFilterForm::class)),
     *   @SWG\Response(response=201, description="Visits found", @Model(type=VisitCollectionResponse::class)),
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
        $convertedFilter = $this->stringConverter->toString($filter);

        $response = new CollectionResponse($this->visitManager->search($filter, $filter->getPageable()),
            "rest_admin_get_searched_visits", ["filter" => $convertedFilter]);

        $this->logger->info("Searching visits - result information",
            array ("filter" => $filter, "response" => $response));

        $location = $this->router->generate("rest_admin_get_searched_visits", array ("filter" => $convertedFilter),
            Router::ABSOLUTE_URL);

        return $this->buildJsonResponse($response, Response::HTTP_CREATED, array ("Location" => $location));
    }


    /**
     * Gets searched visits from the base 64 JSON string filter
     *
     * @Rest\Get(path="/searches/{filter}", name="rest_admin_get_searched_visits")
     *
     * @Operation(tags={ "Visits" },
     *   @SWG\Parameter(
     *     name="filter", in="path", type="string", required=true, description="Base 64 JSON string filter"),
     *   @SWG\Response(response=200, description="Visits found", @Model(type=VisitCollectionResponse::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="Unsupported base64 string conversion")
     * )
     *
     * @param string $filter
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getSearchedVisitsAction(string $filter)
    {
        $this->logger->debug("Getting searched visits from a base 64 string filter", array ("filter" => $filter));

        try
        {
            /** @var VisitFilter $visitFilter */
            $visitFilter = $this->stringConverter->toObject($filter, VisitFilter::class);
        }
        catch (UnsupportedSerializationException $e)
        {
            throw new NotFoundHttpException("No filter found with the given base64 string", $e);
        }

        $response = new CollectionResponse(
            $this->visitManager->search($visitFilter, $visitFilter->getPageable()),
            "rest_admin_get_searched_visits", array ("filter" => $filter));

        $this->logger->info("Searching visits by filtering - result information",
            array ("filter" => $visitFilter, "response" => $response));

        return $this->buildJsonResponse($response);
    }

}
