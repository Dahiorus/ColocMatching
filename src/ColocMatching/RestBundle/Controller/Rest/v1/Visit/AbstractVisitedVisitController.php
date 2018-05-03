<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\DTO\Visit\VisitableDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Filter\VisitFilterForm;
use ColocMatching\CoreBundle\Manager\DtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Visit\VisitDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\PageRequest;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\CoreBundle\Validator\FormValidator;
use ColocMatching\RestBundle\Controller\Response\CollectionResponse;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use ColocMatching\RestBundle\Security\Authorization\Voter\VisitVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

abstract class AbstractVisitedVisitController extends AbstractRestController
{
    /** @var VisitDtoManagerInterface */
    private $visitManager;

    /** @var DtoManagerInterface */
    private $visitedManager;

    /** @var FormValidator */
    private $formValidator;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, VisitDtoManagerInterface $visitManager,
        DtoManagerInterface $visitedManager, FormValidator $formValidator)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->visitManager = $visitManager;
        $this->formValidator = $formValidator;
        $this->visitedManager = $visitedManager;
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

        $this->logger->info("Listing visits on a visited entity", array_merge(array ("id" => $id), $parameters));

        /** @var VisitableDto $visited */
        $visited = $this->getVisitedAndEvaluateRight($id);

        $pageable = PageRequest::create($parameters);
        $response = new PageResponse(
            $this->visitManager->listByVisited($visited, $pageable),
            "rest_get_user_visits", array_merge(array ("id" => $id), $parameters),
            $pageable, $this->visitManager->countByVisited($visited));

        $this->logger->info("Listing visits on a visited - result information", array ("response" => $response));

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
        $this->logger->info("Searching visits on a visited entity",
            array ("id" => $id, "postParams" => $request->request->all()));

        $this->getVisitedAndEvaluateRight($id);

        /** @var VisitFilter $filter */
        $filter = $this->formValidator->validateFilterForm(VisitFilterForm::class, new VisitFilter(),
            $request->request->all());
        $filter->setVisitedId($id);
        $filter->setVisitedClass($this->getVisitedClass());

        $response = new CollectionResponse(
            $this->visitManager->search($filter, $filter->getPageable()), $this->getSearchRoute(), array ("id" => $id));

        $this->logger->info("Searching visits on a visited - result information",
            array ("filter" => $filter, "response" => $response));

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
     * Gets the route name to {@see AbstractVisitedVisitController::searchVisitsAction()}
     * @return string
     */
    abstract protected function getSearchRoute() : string;
}