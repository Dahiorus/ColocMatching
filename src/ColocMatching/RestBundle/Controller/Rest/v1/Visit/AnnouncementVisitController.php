<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Filter\VisitFilterForm;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Visit\VisitDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageRequest;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\CoreBundle\Validator\FormValidator;
use ColocMatching\RestBundle\Controller\Response\CollectionResponse;
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

    /** @var FormValidator */
    private $formValidator;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, VisitDtoManagerInterface $visitManager,
        AnnouncementDtoManagerInterface $announcementManager, FormValidator $formValidator)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->visitManager = $visitManager;
        $this->formValidator = $formValidator;
        $this->announcementManager = $announcementManager;
    }


    /**
     * Lists the visits on one announcement with pagination
     *
     * @Rest\Get(name="rest_get_announcement_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", map=true, nullable=true, requirements="\w+,(asc|desc)", default="createdAt,desc",
     *   allowBlank=false, description="Sorting parameters")
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

        $this->logger->info("Listing visits of one announcement", array_merge(array ("id" => $id), $parameters));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->getVisitedAndEvaluateRight($id);

        $pageable = PageRequest::create($parameters);
        $response = new PageResponse(
            $this->visitManager->listByVisited($announcement, $pageable),
            "rest_get_announcement_visits", array_merge(array ("id" => $id), $parameters),
            $pageable, $this->visitManager->countByVisited($announcement));

        $this->logger->info("Listing visits of one announcement - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Searches visits on an announcement by criteria
     *
     * @Rest\Post(path="/searches", name="rest_search_announcement_visits")
     * @Rest\RequestParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\RequestParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\RequestParam(name="sorts", map=true, nullable=true, requirements="\w+,(asc|desc)",
     *     default="createdAt,desc", allowBlank=false, description="Sorting parameters")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function searchVisitsAction(int $id, ParamFetcher $paramFetcher, Request $request)
    {
        $this->logger->info("Searching visits on an announcement",
            array ("id" => $id, "postParams" => $request->request->all()));

        $this->getVisitedAndEvaluateRight($id);

        $pageable = PageRequest::create($this->extractPageableParameters($paramFetcher));
        /** @var VisitFilter $filter */
        $filter = $this->formValidator->validateFilterForm(VisitFilterForm::class, new VisitFilter(),
            $request->request->all());
        $filter->setVisitedId($id);
        $filter->setVisitedClass(Announcement::class);

        $response = new CollectionResponse(
            $this->visitManager->search($filter, $pageable), "rest_search_announcement_visits", array ("id" => $id));

        $this->logger->info("Searching visits on an announcement - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response);
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