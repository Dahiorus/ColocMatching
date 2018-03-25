<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\Announcement\HousingDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Security\User\JwtUserExtractor;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for resource /announcements/{id}/housing
 *
 * @Rest\Route(path="/announcements/{id}/housing", requirements={"id"="\d+"},
 *   service="coloc_matching.rest.housing_controller")
 *
 * @author Dahiorus
 */
class HousingController extends AbstractRestController
{
    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var JwtUserExtractor */
    private $requestUserExtractor;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AnnouncementDtoManagerInterface $announcementManager, JwtUserExtractor $requestUserExtractor)
    {
        parent::__construct($logger, $serializer);
        $this->announcementManager = $announcementManager;
        $this->requestUserExtractor = $requestUserExtractor;
    }


    /**
     * Gets the housing of an existing announcement
     *
     * @Rest\Get(name="rest_get_announcement_housing")
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getHousingAction(int $id)
    {
        $this->logger->info("Getting the housing of an announcement", array ("id" => $id));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        /** @var HousingDto $housing */
        $housing = $this->announcementManager->getHousing($announcement);

        return $this->buildJsonResponse($housing, Response::HTTP_OK);
    }


    /**
     * Updates the housing of an existing announcement
     *
     * @Rest\Put(name="rest_update_announcement_housing")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function updateHousingAction(int $id, Request $request)
    {
        $this->logger->info("Putting an announcement housing",
            array ("id" => $id, "putParams" => $request->request->all()));

        return $this->handleUpdateHousingRequest($id, $request, true);
    }


    /**
     * Updates (partial) the housing of an existing announcement
     *
     * @Rest\Patch(name="rest_patch_announcement_housing")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function patchHousingAction(int $id, Request $request)
    {
        $this->logger->info("Patching an announcement housing",
            array ("id" => $id, "patchParams" => $request->request->all()));

        return $this->handleUpdateHousingRequest($id, $request, false);
    }


    /**
     * Handles update request on a housing
     *
     * @param int $id The announcement identifier
     * @param Request $request The request to handle
     * @param bool $fullUpdate If the operation is a full or partial update
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws InvalidFormException
     */
    private function handleUpdateHousingRequest(int $id, Request $request, bool $fullUpdate)
    {
        $user = $this->requestUserExtractor->getAuthenticatedUser($request);
        $this->evaluateUserAccess($id == $user->getAnnouncementId(),
            "Only the announcement creator can update the housing");

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        /** @var HousingDto $housing */
        $housing = $this->announcementManager->updateHousing($announcement, $request->request->all(), $fullUpdate);

        $this->logger->info("Housing updated", array ("response" => $housing));

        return $this->buildJsonResponse($housing, Response::HTTP_OK);
    }
}