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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
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


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, AnnouncementDtoManagerInterface $announcementManager)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);
        $this->announcementManager = $announcementManager;
    }


    /**
     * Uploads a new picture for an existing announcement
     *
     * @Rest\Post(name="rest_upload_announcement_picture")
     * @Rest\FileParam(name="file", image=true, nullable=false, description="The picture to upload")
     *
     * @Operation(tags={ "Announcement" }, consumes={ "multipart/form-data" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(name="file", in="formData", type="file", required=true, description="The announcement picture"),
     *   @SWG\Response(response=200, description="Picture uploaded", @Model(type=AnnouncementPictureDto::class)),
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
    public function uploadAnnouncementPictureAction(int $id, Request $request)
    {
        $this->logger->debug("Uploading a new picture for an existing announcement", array ("id" => $id));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        $this->evaluateUserAccess(AnnouncementVoter::ADD_PICTURE, $announcement);
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
     * @Operation(tags={ "Announcement" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(in="path", name="pictureId", type="integer", required=true,
     *     description="The picture identifier"),
     *   @SWG\Response(response=200, description="Picture deleted"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No announcement found")
     * )
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
        $this->logger->debug("Deleting a picture of an existing announcement",
            array ("id" => $id, "pictureId" => $pictureId));

        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($id);
        $this->evaluateUserAccess(AnnouncementVoter::DELETE, $announcement);

        $picture = new AnnouncementPictureDto();
        $picture->setId($pictureId);

        try
        {
            $this->announcementManager->deleteAnnouncementPicture($announcement, $picture);

            $this->logger->info("Announcement picture deleted", array ("picture" => $picture));
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->warning("Trying to delete a non existing picture from an announcement",
                array ("announcement" => $announcement, "exception" => $e));
        }

        return new JsonResponse("Picture deleted");
    }

}
