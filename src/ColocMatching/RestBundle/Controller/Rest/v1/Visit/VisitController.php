<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\DTO\Visit\VisitDto;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Filter\VisitFilterType;
use ColocMatching\CoreBundle\Manager\Visit\VisitDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\FilterFactory;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Rest\Route(path="/visits", service="coloc_matching.rest.visit_controller")
 * @Security(expression="has_role('ROLE_API')")
 */
class VisitController extends AbstractRestController
{
    /** @var VisitDtoManagerInterface */
    private $visitManager;

    /** @var FilterFactory */
    private $filterBuilder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, VisitDtoManagerInterface $visitManager,
        FilterFactory $filterBuilder)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->visitManager = $visitManager;
        $this->filterBuilder = $filterBuilder;
    }


    /**
     * Lists visits
     *
     * @Rest\Get(name="rest_get_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="20")
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results",
     *   default="createdAt")
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$",
     *   default="desc")
     *
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getVisitsAction(ParamFetcher $paramFetcher, Request $request)
    {
        $pageable = $this->extractPageableParameters($paramFetcher);

        $this->logger->info("Listing visits", $pageable);

        /** @var PageableFilter $filter */
        $filter = $this->filterBuilder->createPageableFilter($pageable["page"],
            $pageable["size"], $pageable["order"], $pageable["sort"]);
        /** @var VisitDto[] $visits */
        $visits = $this->visitManager->list($filter);
        /** @var PageResponse $response */
        $response = $this->createPageResponse($visits,
            $this->visitManager->countAll(), $filter, $request);

        $this->logger->info("Listing visits - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            $response->hasNext() ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Searches visits
     *
     * @Rest\Post(path="/searches", name="rest_search_visits")
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function searchVisitsAction(Request $request)
    {
        $this->logger->info("Searching visits", array ("request" => $request));

        /** @var VisitFilter $filter */
        $filter = $this->filterBuilder->buildCriteriaFilter(VisitFilterType::class, new VisitFilter(),
            $request->request->all());
        /** @var VisitDto[] $visits */
        $visits = $this->visitManager->search($filter);
        /** @var PageResponse $response */
        $response = $this->createPageResponse($visits,
            $this->visitManager->countBy($filter), $filter, $request);

        $this->logger->info("Searching visits - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            $response->hasNext() ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }

}