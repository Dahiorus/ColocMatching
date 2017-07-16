<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\Controller\Response\EntityResponse;
use ColocMatching\CoreBundle\Controller\Rest\RestController;
use ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\Announcement\HousingControllerInterface;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for resource /announcements/{id}/housing
 *
 * @Rest\Route("/announcements/{id}/housing")
 *
 * @author Dahiorus
 */
class HousingController extends RestController implements HousingControllerInterface {

    /**
     * Gets the housing of an existing announcement
     *
     * @Rest\Get("", name="rest_get_announcement_housing")
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function getHousingAction(int $id) {
        $this->get("logger")->info("Getting the housing of an existing announcement", array ("id" => $id));

        /** @var Announcement */
        $announcement = $this->get("coloc_matching.core.announcement_manager")->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse(
            $announcement->getHousing());

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Updates the housing of an existing announcement
     *
     * @Rest\Put("", name="rest_update_announcement_housing")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function updateHousingAction(int $id, Request $request) {
        $this->get("logger")->info("Putting an announcement's housing", array ("id" => $id, "request" => $request));

        return $this->handleUpdateHousingRequest($id, $request, true);
    }


    /**
     * Updates (partial) the housing of an existing announcement
     *
     * @Rest\Patch("", name="rest_patch_announcement_housing")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function patchHousingAction(int $id, Request $request) {
        $this->get("logger")->info("Patching an announcement's housing", array ("id" => $id, "request" => $request));

        return $this->handleUpdateHousingRequest($id, $request, false);
    }


    private function handleUpdateHousingRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        /** @var Announcement */
        $announcement = $manager->read($id);

        try {
            $housing = $manager->updateHousing($announcement, $request->request->all(), $fullUpdate);
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($housing);

            $this->get("logger")->info("Housing updated", array ("response" => $response));

            return $this->buildJsonResponse($response, Response::HTTP_OK);
        } catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a housing",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }
}