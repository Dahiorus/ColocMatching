<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\Visit\VisitDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Filter\VisitFilterType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Visit\VisitDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\FilterFactory;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use ColocMatching\RestBundle\Security\Authorization\Voter\VisitVoter;
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
 * REST controller for resources /announcements/visits and /announcements/{id}/visits
 *
 * @Rest\Route(path="/announcements/{id}/visits", service="coloc_matching.rest.announcement_visit_controller")
 * @Security(expression="has_role('ROLE_USER')")
 *
 * @author Dahiorus
 */
class AnnouncementVisitController extends AbstractRestController
{
    /** @var VisitDtoManagerInterface */
    private $visitManager;

    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var FilterFactory */
    private $filterBuilder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, VisitDtoManagerInterface $visitManager,
        AnnouncementDtoManagerInterface $announcementManager, FilterFactory $filterBuilder)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->visitManager = $visitManager;
        $this->filterBuilder = $filterBuilder;
        $this->announcementManager = $announcementManager;
    }


    /**
     * Lists the visits on one announcement with pagination
     *
     * @Rest\Get(name="rest_get_announcement_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="20")
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results",
     *   default="createdAt")
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$",
     *   default="desc")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getVisitsAction(int $id, ParamFetcher $paramFetcher, Request $request)
    {
        $pageable = $this->extractPageableParameters($paramFetcher);

        $this->logger->info("Listing visits of one announcement", array ("announcementId" => $id, $pageable));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->getVisitedAndEvaluateRight($id);

        /** @var PageableFilter $filter */
        $filter = $this->filterBuilder->createPageableFilter($pageable["page"],
            $pageable["size"], $pageable["order"], $pageable["sort"]);

        /** @var VisitDto[] $visits */
        $visits = $this->visitManager->listByVisited($announcement, $filter);
        /** @var PageResponse $response */
        $response = $this->createPageResponse($visits, $this->visitManager->countByVisited($announcement), $filter,
            $request);

        $this->logger->info("Listing visits of one announcement - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Searches visits on an announcement by criteria
     *
     * @Rest\Post(path="/searches", name="rest_search_announcement_visits")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function searchVisitsAction(int $id, Request $request)
    {
        $this->logger->info("Searching visits on an announcement", array ("id" => $id, "request" => $request));

        $this->getVisitedAndEvaluateRight($id);

        /** @var VisitFilter $filter */
        $filter = $this->filterBuilder->buildCriteriaFilter(VisitFilterType::class,
            new VisitFilter(), $request->request->all());
        $filter->setVisitedId($id);
        $filter->setVisitedClass(Announcement::class);

        /** @var VisitDto[] $visits */
        $visits = $this->visitManager->search($filter);
        /** @var PageResponse $response */
        $response = $this->createPageResponse($visits, $this->visitManager->countBy($filter), $filter, $request);

        $this->logger->info("Searching visits on an announcement - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Gets the visited entity and evaluates access to the service
     *
     * @param int $id The visited entity identifier
     *
     * @return AnnouncementDto
     * @throws \ColocMatching\CoreBundle\Exception\EntityNotFoundException
     */
    private function getVisitedAndEvaluateRight(int $id) : AbstractDto
    {
        /** @var AbstractDto $visited */
        $visited = $this->announcementManager->read($id);
        $this->evaluateUserAccess(VisitVoter::VIEW, $visited);

        return $visited;
    }

}