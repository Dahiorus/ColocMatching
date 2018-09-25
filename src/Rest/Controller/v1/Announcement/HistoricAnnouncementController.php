<?php

namespace App\Rest\Controller\v1\Announcement;

use App\Core\DTO\Announcement\HistoricAnnouncementDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\Announcement\HistoricAnnouncementDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\Order;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Rest\Controller\Response\PageResponse;
use App\Rest\Controller\v1\AbstractRestController;
use App\Rest\Security\Authorization\Voter\HistoricAnnouncementVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for the resource /history/announcements
 *
 * @Rest\Route(path="/history/announcements")
 *
 * @author Dahiorus
 */
class HistoricAnnouncementController extends AbstractRestController
{
    /** @var HistoricAnnouncementDtoManagerInterface */
    private $historicAnnouncementManager;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker,
        HistoricAnnouncementDtoManagerInterface $historicAnnouncementManager)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->historicAnnouncementManager = $historicAnnouncementManager;
    }


    /**
     * Gets an existing historic announcement
     *
     * @Rest\Get(path="/{id}", name="rest_get_historic_announcement", requirements={"id"="\d+"})
     *
     * @Operation(tags={ "Announcement - history" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Response(
     *     response=200, description="Historic announcement found", @Model(type=HistoricAnnouncementDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No historic announcement found"),
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function getHistoricAnnouncementAction(int $id)
    {
        $this->logger->debug("Getting an existing historic announcement", array ("id" => $id));

        /** @var HistoricAnnouncementDto $announcement */
        $announcement = $this->historicAnnouncementManager->read($id);

        $this->evaluateUserAccess(HistoricAnnouncementVoter::GET, $announcement);

        $this->logger->info("One historic announcement found", array ("response" => $announcement));

        return $this->buildJsonResponse($announcement, Response::HTTP_OK);
    }


    /**
     * Gets comments of a historic announcement
     *
     * @Rest\Get(path="/{id}/comments", name="rest_get_historic_announcement_comments", requirements={"id"="\d+"})
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="10")
     *
     * @Operation(tags={ "Announcement - history" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Response(response=200, description="Historic announcement comments found"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=206, description="Partial content"),
     * )
     *
     * @param int $id
     * @param ParamFetcher $fetcher
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getCommentsAction(int $id, ParamFetcher $fetcher)
    {
        $page = $fetcher->get("page", true);
        $size = $fetcher->get("size", true);

        $this->logger->debug("Listing a historic announcement comments",
            array ("id" => $id, "page" => $page, "size" => $size));

        /** @var HistoricAnnouncementDto $announcement */
        $announcement = $this->historicAnnouncementManager->read($id);

        $this->evaluateUserAccess(HistoricAnnouncementVoter::GET, $announcement);

        $pageable = new PageRequest($page, $size, array ("createdAt" => Order::DESC));
        $response = new PageResponse(
            $this->historicAnnouncementManager->getComments($announcement, $pageable),
            "rest_get_historic_announcement_comments", array ("id" => $id, "page" => $page, "size" => $size),
            $pageable, $this->historicAnnouncementManager->countComments($announcement));

        $this->logger->info("Listing a historic announcement comments - result information",
            array ("response" => $response));

        return $this->buildJsonResponse($response,
            $response->hasNext() ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }

}
