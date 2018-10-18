<?php

namespace App\Rest\Controller\v1\Visit;

use App\Core\DTO\AbstractDto;
use App\Core\DTO\Visit\VisitableDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\UnsupportedSerializationException;
use App\Core\Form\Type\Filter\VisitFilterForm;
use App\Core\Manager\DtoManagerInterface;
use App\Core\Manager\Visit\VisitDtoManagerInterface;
use App\Core\Repository\Filter\Converter\StringConverterInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Repository\Filter\VisitFilter;
use App\Core\Validator\FormValidator;
use App\Rest\Controller\Response\CollectionResponse;
use App\Rest\Controller\Response\PageResponse;
use App\Rest\Controller\v1\AbstractRestController;
use App\Rest\Security\Authorization\Voter\VisitVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

abstract class AbstractVisitedVisitController extends AbstractRestController
{
    /** @var VisitDtoManagerInterface */
    private $visitManager;

    /** @var DtoManagerInterface */
    private $visitedManager;

    /** @var FormValidator */
    private $formValidator;

    /** @var RouterInterface */
    private $router;

    /** @var StringConverterInterface */
    private $stringConverter;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, VisitDtoManagerInterface $visitManager,
        DtoManagerInterface $visitedManager, FormValidator $formValidator, RouterInterface $router,
        StringConverterInterface $stringConverter)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->visitManager = $visitManager;
        $this->formValidator = $formValidator;
        $this->visitedManager = $visitedManager;
        $this->router = $router;
        $this->stringConverter = $stringConverter;
    }


    /**
     * Lists visits done on a visited entity
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getVisitsAction(int $id, ParamFetcher $paramFetcher)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->debug("Listing visits on a visited entity", array_merge(array ("id" => $id), $parameters));

        /** @var VisitableDto $visited */
        $visited = $this->getVisitedAndEvaluateRight($id);

        $pageable = PageRequest::create($parameters);
        $response = new PageResponse(
            $this->visitManager->listByVisited($visited, $pageable),
            "rest_get_user_visits", array_merge(array ("id" => $id), $parameters),
            $pageable, $this->visitManager->countByVisited($visited));

        $this->logger->info("Listing visits on a visited entity - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Searches visits done on a visited entity
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws InvalidFormException
     */
    public function searchVisitsAction(int $id, Request $request)
    {
        $this->logger->debug("Searching visits on a visited entity",
            array ("id" => $id, "postParams" => $request->request->all()));

        $this->getVisitedAndEvaluateRight($id);

        /** @var VisitFilter $filter */
        $filter = $this->formValidator->validateFilterForm(VisitFilterForm::class, new VisitFilter(),
            $request->request->all());
        $filter->setVisitedId($id);
        $filter->setVisitedClass($this->getVisitedClass());

        $convertedFilter = $this->stringConverter->toString($filter);

        $response = new CollectionResponse(
            $this->visitManager->search($filter, $filter->getPageable()),
            $this->getSearchRoute(), array ("id" => $id, "filter" => $convertedFilter),
            $this->visitManager->countBy($filter));

        $this->logger->info("Searching visits on a visited entity - result information",
            array ("filter" => $filter, "response" => $response));

        $location = $this->router->generate($this->getSearchRoute(), array ("id" => $id, "filter" => $convertedFilter),
            Router::ABSOLUTE_URL);

        return $this->buildJsonResponse($response, Response::HTTP_CREATED, array ("Location" => $location));
    }


    /**
     * Gets searched visits on a visited entity from the base 64 JSON string filter
     *
     * @param int $id
     * @param string $filter
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getSearchedVisitsAction(int $id, string $filter)
    {
        $this->logger->debug("Getting searched visits on a visited entity from a base 64 string filter",
            array ("id" => $id, "filter" => $filter));

        try
        {
            /** @var VisitFilter $visitFilter */
            $visitFilter = $this->stringConverter->toObject($filter, VisitFilter::class);
            $visitFilter->setVisitedId($id);
            $visitFilter->setVisitedClass($this->getVisitedClass());
        }
        catch (UnsupportedSerializationException $e)
        {
            throw new NotFoundHttpException("No filter found with the given base64 string", $e);
        }

        $response = new CollectionResponse($this->visitManager->search(
            $visitFilter, $visitFilter->getPageable()), $this->getSearchRoute(),
            array ("id" => $id, "filter" => $filter), $this->visitManager->countBy($visitFilter));

        $this->logger->info("Searching visits on a visited entity - result information",
            array ("filter" => $visitFilter, "response" => $response));

        return $this->buildJsonResponse($response);
    }


    /**
     * Gets the visited entity and evaluates access to the service
     *
     * @param int $id The visited entity identifier
     *
     * @return AbstractDto
     * @throws EntityNotFoundException
     */
    protected function getVisitedAndEvaluateRight(int $id) : AbstractDto
    {
        $visited = $this->visitedManager->read($id);
        $this->evaluateUserAccess(VisitVoter::VIEW, $visited);

        return $visited;
    }


    /**
     * Gets the visited entity class
     * @return string
     */
    abstract protected function getVisitedClass() : string;


    /**
     * Gets the route name to {@see AbstractVisitedVisitController::getVisitsAction()}
     * @return string
     */
    abstract protected function getListRoute() : string;


    /**
     * Gets the route name to {@see AbstractVisitedVisitController::getSearchedVisitsAction()}
     * @return string
     */
    abstract protected function getSearchRoute() : string;
}