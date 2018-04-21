<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Filter\VisitFilterType;
use ColocMatching\CoreBundle\Manager\Visit\VisitDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageRequest;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\CoreBundle\Validator\FormValidator;
use ColocMatching\RestBundle\Controller\Response\CollectionResponse;
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
     * @Rest\Get(name="rest_get_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", map=true, nullable=true, requirements="\w+,(asc|desc)", default="createdAt,desc",
     *   allowBlank=false, description="Sorting parameters")
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getVisitsAction(ParamFetcher $paramFetcher)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->info("Listing visits", $parameters);

        $pageable = PageRequest::create($parameters);
        $response = new PageResponse(
            $this->visitManager->list($pageable),
            "rest_get_visits", $paramFetcher->all(),
            $pageable, $this->visitManager->countAll());

        $this->logger->info("Listing visits - result information",
            array ("pageable" => $pageable, "response" => $response));

        return $this->buildJsonResponse($response, ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT :
            Response::HTTP_OK);
    }


    /**
     * Searches visits
     *
     * @Rest\Post(path="/searches", name="rest_search_visits")
     * @Rest\RequestParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\RequestParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\RequestParam(name="sorts", map=true, nullable=true, requirements="\w+,(asc|desc)",
     *   default="createdAt,desc", allowBlank=false, description="Sorting parameters")
     *
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function searchVisitsAction(ParamFetcher $paramFetcher, Request $request)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->info("Searching specific visits", array ("postParams" => $request->request->all()));

        $filter = $this->formValidator->validateFilterForm(VisitFilterType::class, new VisitFilter(),
            $request->request->all());
        $pageable = PageRequest::create($parameters);
        $response = new CollectionResponse($this->visitManager->search($filter, $pageable), "rest_search_visits");

        $this->logger->info("Searching visits - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response);
    }

}