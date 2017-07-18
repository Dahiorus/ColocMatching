<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1\Group;

use ColocMatching\CoreBundle\Controller\Response\EntityResponse;
use ColocMatching\CoreBundle\Controller\Rest\RestController;
use ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\Group\GroupPictureControllerInterface;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Group\GroupPicture;
use ColocMatching\CoreBundle\Exception\GroupNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Manager\Group\GroupManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST Controller for the resource /groups
 *
 * @Rest\Route("/groups/{id}/picture")
 *
 * @author Dahiorus
 */
class GroupPictureController extends RestController implements GroupPictureControllerInterface {

    /**
     * Gets a group's picture
     *
     * @Rest\Get("", name="rest_get_group_picture")
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function getGroupPictureAction(int $id) {
        $this->get("logger")->info("Getting a group's picture", array ("id" => $id));

        /** @var Group */
        $group = $this->get("coloc_matching.core.group_manager")->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($group->getPicture());

        $this->get("logger")->info("Group's picture found", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Uploads a file as the picture of an existing group
     *
     * @Rest\Post("", name="rest_upload_group_picture")
     * @Rest\FileParam(name="file", image=true, nullable=false, description="The picture to upload")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function uploadGroupPictureAction(int $id, Request $request) {
        $this->get("logger")->info("Uploading a picture for a group", array ("id" => $id, "request" => $request));

        /** @var GroupManagerInterface */
        $manager = $this->get("coloc_matching.core.group_manager");
        /** @var Group */
        $group = $manager->read($id);

        try {
            /** @var GroupPicture */
            $picture = $manager->uploadGroupPicture($group, $request->files->get("file"));
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($picture);

            $this->get("logger")->info("Group picture uploaded", array ("response" => $response));

            return $this->buildJsonResponse($response, Response::HTTP_OK);
        } catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to upload a picture for a group",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }


    /**
     * Deletes the picture of an existing group
     *
     * @Rest\Delete("", name="rest_delete_group_picture")
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function deleteGroupPictureAction(int $id) {
        $this->get("logger")->info("Deleting a group's picture", array ("id" => $id));

        /** @var GroupManagerInterface */
        $manager = $this->get('coloc_matching.core.group_manager');

        $manager->deleteGroupPicture($manager->read($id));

        return new JsonResponse("Group's picture deleted");
    }
}