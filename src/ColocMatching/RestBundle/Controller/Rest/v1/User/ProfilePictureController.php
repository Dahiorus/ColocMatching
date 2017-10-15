<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\Entity\User\ProfilePicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserManagerInterface;
use ColocMatching\RestBundle\Controller\Response\EntityResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\User\ProfilePictureControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for the resource /users/{id}/picture
 *
 * @Rest\Route("/users/{id}/picture")
 *
 * @author Dahiorus
 */
class ProfilePictureController extends RestController implements ProfilePictureControllerInterface {

    /**
     * Gets a user's picture
     *
     * @Rest\Get("", name="rest_get_user_picture")
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function getPictureAction(int $id) {
        $this->get("logger")->info("Getting a user's picture", array ("id" => $id));

        /** @var User */
        $user = $this->get("coloc_matching.core.user_manager")->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($user->getPicture());

        $this->get("logger")->info("User's picture found", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Uploads a file as the profile picture of an existing user
     *
     * @Rest\Post("", name="rest_upload_user_picture")
     * @Rest\FileParam(name="file", image=true, nullable=false, description="The picture to upload")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function uploadPictureAction(int $id, Request $request) {
        $this->get("logger")->info("Uploading a profile picture for a user",
            array ("id" => $id, "request" => $request));

        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");
        /** @var User */
        $user = $manager->read($id);
        /** @var ProfilePicture */
        $picture = $manager->uploadProfilePicture($user, $request->files->get("file"));
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($picture);

        $this->get("logger")->info("Profie picture uploaded", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Deletes the profile picture of an existing user
     *
     * @Rest\Delete("", name="rest_delete_user_picture")
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function deletePictureAction(int $id) {
        /** @var UserManagerInterface */
        $manager = $this->get('coloc_matching.core.user_manager');

        $this->get('logger')->info("Deleting a User's profile picture", array ("id" => $id));

        $manager->deleteProfilePicture($manager->read($id));

        return new JsonResponse("User's profile picture deleted");
    }
}