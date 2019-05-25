<?php

namespace App\Rest\Controller\v1\Visit;

use App\Core\DTO\AbstractDto;
use App\Core\DTO\Visit\VisitableDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\DtoManagerInterface;
use App\Core\Manager\Visit\VisitDtoManagerInterface;
use App\Core\Repository\Filter\Converter\StringConverterInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Repository\Filter\VisitFilter;
use App\Rest\Controller\Response\PageResponse;
use App\Rest\Controller\v1\AbstractRestController;
use App\Rest\Security\Authorization\Voter\VisitVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

abstract class AbstractVisitedVisitController extends AbstractRestController
{
    /** @var VisitDtoManagerInterface */
    private $visitManager;

    /** @var DtoManagerInterface */
    private $visitedManager;

    /** @var StringConverterInterface */
    private $stringConverter;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, VisitDtoManagerInterface $visitManager,
        DtoManagerInterface $visitedManager, StringConverterInterface $stringConverter)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->visitManager = $visitManager;
        $this->visitedManager = $visitedManager;
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
        /** @var VisitFilter $filter */
        $filter = $this->extractQueryFilter(VisitFilter::class, $paramFetcher, $this->stringConverter);
        $pageable = PageRequest::create($parameters);

        $this->logger->debug("Listing visits on a visited entity",
            array_merge($parameters, ["id" => $id, "filter" => $filter]));

        /** @var VisitableDto $visited */
        $visited = $this->getVisitedAndEvaluateRight($id);

        if (empty($filter))
        {
            $result = $this->visitManager->listByVisited($visited, $pageable);
        }
        else
        {
            $filter->setVisitedId($id);
            $result = $this->visitManager->search($filter, $pageable);
        }

        $response = new PageResponse($result, $this->getListRoute(), array_merge(array ("id" => $id), $parameters));

        $this->logger->info("Listing visits on a visited entity - result information", array ("response" => $response));

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
     *
     * @return string
     */
    abstract protected function getVisitedClass() : string;


    /**
     * Gets the route name to {@see AbstractVisitedVisitController::getVisitsAction()}
     *
     * @return string
     */
    abstract protected function getListRoute() : string;

}
