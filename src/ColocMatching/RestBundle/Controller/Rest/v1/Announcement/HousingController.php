<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\Announcement\HousingDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Announcement\HousingDtoForm;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use ColocMatching\RestBundle\Security\Authorization\Voter\AnnouncementVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for resource /announcements/{id}/housing
 *
 * @Rest\Route(path="/announcements/{id}/housing", requirements={"id"="\d+"})
 *
 * @author Dahiorus
 */
class HousingController extends AbstractRestController
{
    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, AnnouncementDtoManagerInterface $announcementManager)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);
        $this->announcementManager = $announcementManager;
    }


    /**
     * Gets the housing of an existing announcement
     *
     * @Rest\Get(name="rest_get_announcement_housing")
     *
     * @Operation(tags={ "Announcement - housing" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Response(response=200, description="Housing found", @Model(type=HousingDto::class)),
     *   @SWG\Response(response=404, description="No announcement found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getHousingAction(int $id)
    {
        $this->logger->debug("Getting the housing of an announcement", array ("id" => $id));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        /** @var HousingDto $housing */
        $housing = $this->announcementManager->getHousing($announcement);

        $this->logger->info("Announcement housing found", array ("housing" => $housing));

        return $this->buildJsonResponse($housing, Response::HTTP_OK);
    }


    /**
     * Updates the housing of an existing announcement
     *
     * @Rest\Put(name="rest_update_announcement_housing")
     *
     * @Operation(tags={ "Announcement - housing" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(name="housing", in="body", required=true, description="The housing to update",
     *     @Model(type=HousingDtoForm::class)),
     *   @SWG\Response(response=200, description="Housing updated", @Model(type=HousingDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No announcement found"),
     *   @SWG\Response(response=400, description="Validation error")
     * )
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
        $this->logger->debug("Putting an announcement housing",
            array ("id" => $id, "putParams" => $request->request->all()));

        return $this->handleUpdateHousingRequest($id, $request, true);
    }


    /**
     * Updates (partial) the housing of an existing announcement
     *
     * @Rest\Patch(name="rest_patch_announcement_housing")
     *
     * @Operation(tags={ "Announcement - housing" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(name="housing", in="body", required=true, description="The housing to update",
     *     @Model(type=HousingDtoForm::class)),
     *   @SWG\Response(response=200, description="Housing updated", @Model(type=HousingDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No announcement found"),
     *   @SWG\Response(response=400, description="Validation error")
     * )
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
        $this->logger->debug("Patching an announcement housing",
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
        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        $this->evaluateUserAccess(AnnouncementVoter::UPDATE, $announcement);
        /** @var HousingDto $housing */
        $housing = $this->announcementManager->updateHousing($announcement, $request->request->all(), $fullUpdate);

        $this->logger->info("Housing updated", array ("response" => $housing));

        return $this->buildJsonResponse($housing, Response::HTTP_OK);
    }
}