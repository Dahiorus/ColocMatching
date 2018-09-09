<?php

namespace App\Rest\Controller\v1\User;

use App\Core\DTO\User\ProfilePictureDto;
use App\Core\DTO\User\UserDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Rest\Controller\v1\AbstractRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for the resource /users/{id}/picture
 *
 * @Rest\Route(path="/users/{id}/picture", requirements={"id"="\d+"})
 * @Security(expression="has_role('ROLE_USER')")
 *
 * @author Dahiorus
 */
class ProfilePictureController extends AbstractRestController
{
    /** @var UserDtoManagerInterface */
    private $userManager;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, UserDtoManagerInterface $userManager)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);
        $this->userManager = $userManager;
    }


    /**
     * Uploads a file as the profile picture of an existing user
     *
     * @Rest\Post(name="rest_upload_user_picture")
     * @Rest\FileParam(name="file", image=true, nullable=false, description="The picture to upload")
     *
     * @Operation(tags={ "User" }, consumes={ "multipart/form-data" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(name="file", in="formData", type="file", required=true, description="The profile picture"),
     *   @SWG\Response(response=200, description="Picture uploaded", @Model(type=ProfilePictureDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No user found"),
     *   @SWG\Response(response=400, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function uploadPictureAction(int $id, Request $request)
    {
        $this->logger->debug("Uploading a profile picture for a user",
            array ("id" => $id, "postParams" => $request->files));

        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        /** @var ProfilePictureDto $picture */
        $picture = $this->userManager->uploadProfilePicture($user, $request->files->get("file"));

        $this->logger->info("Profile picture uploaded", array ("response" => $picture));

        return $this->buildJsonResponse($picture, Response::HTTP_OK);
    }


    /**
     * Deletes the profile picture of an existing user
     *
     * @Rest\Delete(name="rest_delete_user_picture")
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(response=204, description="Picture deleted"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No user found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function deletePictureAction(int $id)
    {
        $this->logger->debug("Deleting a User's profile picture", array ("id" => $id));

        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        $this->userManager->deleteProfilePicture($user);

        $this->logger->info("Profile picture deleted", array ("user" => $user));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}