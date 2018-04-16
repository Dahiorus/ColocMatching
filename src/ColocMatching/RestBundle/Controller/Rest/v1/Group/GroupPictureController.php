<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Group;

use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\Group\GroupPictureDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Manager\Group\GroupDtoManagerInterface;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use ColocMatching\RestBundle\Security\Authorization\Voter\GroupVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST Controller for the resource /groups
 *
 * @Rest\Route(path="/groups/{id}/picture", service="coloc_matching.rest.group_picture_controller")
 *
 * @author Dahiorus
 */
class GroupPictureController extends AbstractRestController
{
    /** @var GroupDtoManagerInterface */
    private $groupManager;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, GroupDtoManagerInterface $groupManager)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->groupManager = $groupManager;
    }


    /**
     * Uploads a file as the picture of an existing group
     *
     * @Rest\Post(name="rest_upload_group_picture")
     * @Rest\FileParam(name="file", image=true, nullable=false, description="The picture to upload")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function uploadGroupPictureAction(int $id, Request $request)
    {
        $this->logger->info("Uploading a picture for a group", array ("id" => $id, "request" => $request));

        /** @var GroupDto $group */
        $group = $this->groupManager->read($id);
        $this->evaluateUserAccess(GroupVoter::UPDATE_PICTURE, $group);

        /** @var GroupPictureDto $picture */
        $picture = $this->groupManager->uploadGroupPicture($group, $request->files->get("file"));

        $this->logger->info("Group picture uploaded", array ("response" => $picture));

        return $this->buildJsonResponse($picture, Response::HTTP_OK);
    }


    /**
     * Deletes the picture of an existing group
     *
     * @Rest\Delete(name="rest_delete_group_picture")
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function deleteGroupPictureAction(int $id)
    {
        $this->logger->info("Deleting a group's picture", array ("id" => $id));

        /** @var GroupDto $group */
        $group = $this->groupManager->read($id);
        $this->evaluateUserAccess(GroupVoter::DELETE, $group);

        $this->groupManager->deleteGroupPicture($group);

        return new JsonResponse("Group's picture deleted");
    }

}
