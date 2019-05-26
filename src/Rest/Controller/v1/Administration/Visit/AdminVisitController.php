<?php

namespace App\Rest\Controller\v1\Administration\Visit;

use App\Core\Manager\Visit\VisitDtoManagerInterface;
use App\Core\Repository\Filter\Converter\StringConverterInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Repository\Filter\VisitFilter;
use App\Rest\Controller\Response\PageResponse;
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

    /** @var StringConverterInterface */
    private $stringConverter;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, VisitDtoManagerInterface $visitManager,
        StringConverterInterface $stringConverter)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->visitManager = $visitManager;
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
     * @Rest\QueryParam(
     *   name="q", nullable=true,
     *   description="Search query to filter results (csv), parameters are in the form 'name:value'")
     *
     * @Operation(tags={ "Visits" },
     *   @SWG\Response(response=200, description="Visits found", @Model(type=VisitPageResponse::class)),
     *   @SWG\Response(response=400, description="Invalid search query filter"),
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
        $filter = $this->extractQueryFilter(VisitFilter::class, $paramFetcher, $this->stringConverter);
        $pageable = PageRequest::create($parameters);

        $this->logger->debug("Listing visits", array_merge($parameters, ["filter" => $filter]));

        $result = empty($filter) ? $this->visitManager->list($pageable)
            : $this->visitManager->search($filter, $pageable);
        $response = new PageResponse($result, "rest_admin_get_visits", $paramFetcher->all());

        $this->logger->info("Listing visits - result information", array ("response" => $response));

        return $this->buildJsonResponse($response);
    }

}
