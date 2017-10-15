<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\AnnouncementPictureNotFoundException;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface;
use ColocMatching\RestBundle\Controller\Response\EntityResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\Announcement\AnnouncementPictureControllerInterface;
use Doctrine\Common\Collections\Collection;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for resource /announcements/{id}/pictures
 *
 * @Rest\Route("/announcements/{id}/pictures")
 *
 * @author Dahiorus
 */
class AnnouncementPictureController extends RestController implements AnnouncementPictureControllerInterface {

    /**
     * Gets all pictures of an existing announcement
     *
     * @Rest\Get("", name="rest_get_announcement_pictures")
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function getAnnouncementPicturesAction(int $id) {
        $this->get("logger")->info("Getting all pictures of an existing announcement", array ("id" => $id));

        /** @var Announcement */
        $announcement = $this->get('coloc_matching.core.announcement_manager')->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse(
            $announcement->getPictures());

        $this->get("logger")->info("One announcement found", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Uploads a new picture for an existing announcement
     *
     * @Rest\Post("", name="rest_upload_announcement_picture")
     * @Rest\FileParam(name="file", image=true, nullable=false, description="The picture to upload")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function uploadAnnouncementPictureAction(int $id, Request $request) {
        $this->get("logger")->info("Uploading a new picture for an existing announcement", array ("id" => $id));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get('coloc_matching.core.announcement_manager');
        /** @var Collection */
        $pictures = $manager->uploadAnnouncementPicture($manager->read($id), $request->files->get("file"));
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($pictures);

        $this->get("logger")->info("Announcement picture uploaded", array ("response" => $response));

        return $this->buildJsonResponse($response,
            Response::HTTP_CREATED);
    }


    /**
     * Gets a picture of an existing announcement
     *
     * @Rest\Get("/{pictureId}", name="rest_get_announcement_picture")
     *
     * @param int $id
     * @param int $pictureId
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     * @throws AnnouncementPictureNotFoundException
     */
    public function getAnnouncementPictureAction(int $id, int $pictureId) {
        $this->get("logger")->info("Getting one picture of an existing announcement",
            array ("id" => $id, "pictureId" => $pictureId));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");
        /** @var AnnouncementPicture */
        $picture = $manager->readAnnouncementPicture($manager->read($id), $pictureId);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($picture);

        $this->get("logger")->info("One announcement picture found", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Deletes a picture from an existing announcement
     *
     * @Rest\Delete("/{pictureId}", name="rest_delete_announcement_picture")
     *
     * @param int $id
     * @param int $pictureId
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function deleteAnnouncementPictureAction(int $id, int $pictureId) {
        $this->get("logger")->info("Deleting a picture of an existing announcement",
            array ("id" => $id, "pictureId" => $pictureId));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        try {
            /** @var AnnouncementPicture */
            $picture = $manager->readAnnouncementPicture($manager->read($id), $pictureId);

            $this->get("logger")->info(sprintf("AnnouncementPicture found"), array ("picture" => $picture));

            $manager->deleteAnnouncementPicture($picture);
        }
        catch (AnnouncementPictureNotFoundException $e) {
            // Nothing to do
        }

        return new JsonResponse("AnnouncementPicture deleted", Response::HTTP_OK);
    }
}