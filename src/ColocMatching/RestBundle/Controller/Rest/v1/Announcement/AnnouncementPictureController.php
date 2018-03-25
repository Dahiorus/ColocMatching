<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementPictureDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use ColocMatching\RestBundle\Security\Authorization\Voter\AnnouncementVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for resource /announcements/{id}/pictures
 *
 * @Rest\Route(path="/announcements/{id}/pictures", requirements={"id"="\d+"},
 *   service="coloc_matching.rest.announcement_picture_controller")
 *
 * @author Dahiorus
 */
class AnnouncementPictureController extends AbstractRestController
{
    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AnnouncementDtoManagerInterface $announcementManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct($logger, $serializer);
        $this->announcementManager = $announcementManager;
        $this->authorizationChecker = $authorizationChecker;
    }


    /**
     * Uploads a new picture for an existing announcement
     *
     * @Rest\Post(name="rest_upload_announcement_picture")
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
    public function uploadAnnouncementPictureAction(int $id, Request $request)
    {
        $this->logger->info("Uploading a new picture for an existing announcement", array ("id" => $id));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        $this->evaluateUserAccess($this->authorizationChecker->isGranted(AnnouncementVoter::CREATE,
            $announcement));
        /** @var AnnouncementPictureDto $picture */
        $picture = $this->announcementManager->uploadAnnouncementPicture($announcement, $request->files->get("file"));

        $this->logger->info("Announcement picture uploaded", array ("response" => $picture));

        return $this->buildJsonResponse($picture, Response::HTTP_CREATED);
    }


    /**
     * Deletes a picture from an existing announcement
     *
     * @Rest\Delete("/{pictureId}", name="rest_delete_announcement_picture", requirements={"pictureId"="\d+"})
     *
     * @param int $id
     * @param int $pictureId
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function deleteAnnouncementPictureAction(int $id, int $pictureId)
    {
        $this->logger->info("Deleting a picture of an existing announcement",
            array ("id" => $id, "pictureId" => $pictureId));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        $this->evaluateUserAccess($this->authorizationChecker->isGranted(AnnouncementVoter::CREATE, $announcement));

        $picture = new AnnouncementPictureDto();
        $picture->setId($pictureId);

        $this->announcementManager->deleteAnnouncementPicture($announcement, $picture);

        return new JsonResponse("Picture deleted");
    }

}
