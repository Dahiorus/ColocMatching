<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Controller\Rest\RestDataResponse;
use ColocMatching\CoreBundle\Controller\Rest\RestListResponse;
use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\AddressType;
use ColocMatching\CoreBundle\Form\Type\Announcement\AnnouncementType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManager;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use FOS\RestBundle\Controller\Annotations as Rest;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * REST controller for resource /announcements
 *
 * @author brondon.ung
 */
class AnnouncementController extends Controller {


    /**
     * Get announcements or specified fields with pagination
     *
     * @Rest\Get("", name="rest_get_announcements")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+", default="1")
     * @Rest\QueryParam(name="limit", nullable=true, description="The number of results to return", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results", default="id")
     * @Rest\QueryParam(name="order", nullable=true, description="'asc' if ascending order, 'desc' if descending order", requirements="(asc|desc)", default="asc")
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return for each result")
     * @Rest\QueryParam(name="address", nullable=true, description="The address criteria to filter the announcements")
     * @ApiDoc(
     *   section="Announcements",
     *   description="Get announcements or specified fields with pagination",
     *   resource=true,
     *   statusCodes={
     * 	   200="OK",
     *     206="Partial content",
     *     400="Bad address request"
     *   },
     *   output={ "class"=Announcement::class, "collection"=true }
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAnnouncementsAction(Request $request) {
        $page = $request->query->get("page", RequestConstants::DEFAULT_PAGE);
        $limit = $request->query->get("limit", RequestConstants::DEFAULT_LIMIT);
        $order = $request->query->get("order", RequestConstants::DEFAULT_ORDER);
        $sort = $request->query->get("sort", RequestConstants::DEFAULT_SORT);
        $fields = $request->query->get("fields", null);
        $address = $request->query->get("address", null);
        
        $this->get("logger")->info(
            sprintf("Get Announcements [page: %d | limit: %d | order: '%s' | sort: '%s' | fields: [%s]]", $page, $limit,
                $order, $sort, $fields), [ 'request' => $request]);
        
        /** @var AnnouncementManager */
        $manager = $this->get("coloc_matching.core.announcement_manager");
        
        /** @var AbstractFilter */
        $filter = new AnnouncementFilter();
        $filter->setOffset(($page - 1) * $limit)->setSize($limit)->setOrder($order)->setSort($sort);
        
        if (!empty($fields)) {
            $fields = explode(",", $fields);
        }
        
        /** @var array */
        $announcements = [ ];
        /** @var int */
        $total = 0;
        
        if (empty($address)) {
            $announcements = $manager->list($filter, $fields);
            $total = $manager->countAll();
        }
        else {
            /** @var Address */
            $fullAddress = $this->getAddress($address);
            
            $this->get("logger")->info(
                sprintf(
                    "Get Announcements by address [page: %d | limit: %d | order: '%s' | sort: '%s' | fields: [%s] | address: %s]",
                    $page, $limit, $order, $sort, $fields, $fullAddress), [ 'request' => $request]);
            
            $announcements = $manager->getByAddress($fullAddress, $filter, $fields);
            $total = $manager->countByAddress($fullAddress);
        }
        
        $restList = new RestListResponse($announcements, "/rest/announcements");
        $restList->setTotal($total)->setStart(($page - 1) * $limit)->setOrder($order)->setSort($sort);
        $restList->setRelationLinks($page);
        
        /** @var int */
        $codeStatus = ($restList->getSize() < $restList->getTotal()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK;
        
        $this->get("logger")->info(
            sprintf("Result information : [start: %d | size: %d | total: %d]", $restList->getStart(),
                $restList->getSize(), $restList->getTotal()), [ 'response' => $restList]);
        
        return new JsonResponse($this->get("jms_serializer")->serialize($restList, "json"), $codeStatus,
            [ "Location" => $request->getUri()], true);
    }


    /**
     * Get an existing announcement or specified fields
     *
     * @Rest\Get("/{id}", name="rest_get_announcement")
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return")
     * @ApiDoc(
     *   section="Announcements",
     *   description="Get an exisiting announcement or specified fields",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The announcement id" }
     *   },
     *   output={ "class"=Announcement::class },
     *   statusCodes={
     *     200="OK",
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     404="Announcement not found"
     * })
     *
     * @param int $id
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function getAnnouncementAction(int $id, Request $request) {
        /** @var array */
        $fields = $request->query->get('fields', null);
        
        $this->get("logger")->info(sprintf("Get an announcement by id [id: %d | fields: [%s]]", $id, $fields),
            [ 'id' => $id, 'request' => $request]);
        
        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.announcement_manager');
        
        /** @var Announcement */
        $announcement = (!$fields) ? $manager->read($id) : $manager->read($id, explode(',', $fields));
        $restData = new RestDataResponse($announcement, "/rest/announcements/$id");
        
        $this->get("logger")->info("One announcement found", [ "response" => $restData]);
        
        return new JsonResponse($this->get("jms_serializer")->serialize($restData, "json"), Response::HTTP_OK,
            [ "Location" => $request->getUri()], true);
    }


    /**
     * Create a new announcement for the authenticated user
     *
     * @Rest\Post("", name="rest_create_announcement")
     * @Rest\RequestParam(name="announcement", requirements="array", description="The announcement data to post", nullable=false)
     * @ApiDoc(
     *   section="Announcements",
     *   description="Create a new announcement for the authenticated user",
     *   input={ "class"=AnnouncementType::class },
     *   statusCodes={
     *     201="Created",
     *     400="Invalid form",
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     422="Cannot recreate an announcement"
     *   },
     *   responseMap={
     *     201={ "class"=Announcement::class },
     *     400={ "class"=AnnouncementType::class, "form_errors"=true, "name"="" }
     * })
     *
     * @param Request $request
     * @return JsonResponse
     * @throws JWTDecodeFailureException
     * @throws UnprocessableEntityHttpException
     */
    public function createAnnouncementAction(Request $request) {
        /** @var array */
        $postData = $request->get("announcement", [ ]);
        /** @var User */
        $user = $this->extractUser($request);
        
        $this->get("logger")->info(sprintf("Post a new Announcement"), [ "user" => $user, "request" => $request]);
        
        try {
            /** @var Announcement */
            $announcement = $this->get('coloc_matching.core.announcement_manager')->create($user, $postData);
            $restData = new RestDataResponse($announcement, '/rest/announcements/' . $announcement->getId());
            
            $this->get("logger")->info(sprintf("Announcement created [announcement: %s]", $announcement),
                [ 'response' => $restData]);
            
            return new JsonResponse($this->get("jms_serializer")->serialize($restData, "json"), Response::HTTP_CREATED,
                [ "Location" => $request->getUri()], true);
        }
        catch (InvalidFormDataException $e) {
            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ "Location" => $request->getUri()],
                true);
        }
    }


    /**
     * Update an existing announcement
     *
     * @Rest\Put("/{id}", name="rest_update_announcement")
     * @Rest\RequestParam(name="announcement", requirements="array", description="The announcement data to put", nullable=false)
     * @ApiDoc(
     *   section="Announcements",
     *   description="Update an existing announcement",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The announcement id" }
     *   },
     *   input={ "class"=AnnouncementType::class },
     *   statusCodes={
     *     200="OK",
     *     400="Invalid form data",
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     404="Announcement not found"
     *   },
     *   responseMap={
     *     200={ "class"=Announcement::class },
     *     400={ "class"=AnnouncementType::class, "form_errors"=true, "name"="" }
     * })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function updateAnnouncementAction(int $id, Request $request) {
        $this->get("logger")->info(sprintf("Put an announcement with the following id [id: %d]", $id),
            [ 'id' => $id, 'request' => $request]);
        
        return $this->handleUpdateRequest($id, $request, true);
    }


    /**
     * Patch an existing announcement
     *
     * @Rest\Patch("/{id}", name="rest_patch_announcement")
     * @Rest\RequestParam(name="announcement", requirements="array", description="The announcement data to patch", nullable=false)
     * @ApiDoc(
     *   section="Announcements",
     *   description="Patch an existing announcemeent",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The user id" }
     *   },
     *   input={ "class"=AnnouncementType::class },
     *   statusCodes={
     *     200="OK",
     *     400="Invalid form",
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     404="Announcement not found"
     *   },
     *   responseMap={
     *     200={ "class"=User::class },
     *     400={ "class"=AnnouncementType::class, "form_errors"=true, "name"="" }
     *   })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function patchAnnouncementAction(int $id, Request $request) {
        $this->get("logger")->info(sprintf("Patch an announcement with the following id [id: %d]", $id),
            [ 'id' => $id, 'request' => $request]);
        
        return $this->handleUpdateRequest($id, $request, false);
    }


    /**
     * Delete an existing announcement
     *
     * @Rest\Delete("/{id}", name="rest_delete_announcement")
     * @ApiDoc(
     *   section="Announcements",
     *   description="Delete an existing announcement",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The announcement id" }
     *   },
     *   statusCodes={
     *     200="OK",
     *     401="Unauthorized access",
     *     403="Forbidden access"
     * })
     *
     * @Security(expression="has_role('ROLE_ADMIN')")
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAnnouncementAction(int $id) {
        /** @var UserManager */
        $manager = $this->get('coloc_matching.core.announcement_manager');
        
        $this->get("logger")->info(sprintf("Delete an announcement with the following id [id: %d]", $id),
            [ 'id' => $id]);
        
        /** @var Announcement */
        $announcement = $manager->read($id);
        
        if ($announcement) {
            $this->get("logger")->info(sprintf("Announcement found [announcement: %s]", $announcement),
                [ "announcement" => $announcement]);
            
            $manager->delete($announcement);
        }
        
        return new JsonResponse("Announcement deleted", Response::HTTP_OK, [ ], true);
    }


    /**
     * Get all pictures of an existing announcement
     *
     * @Rest\Get("/{id}/pictures", name="rest_get_announcement_pictures")
     * @ApiDoc(
     *   section="Announcements",
     *   resource=true,
     *   description="Get all pictures of an existing announcement",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The announcement id" }
     *   },
     *   output={ "class"=AnnouncementPicture::class, "collection"=true },
     *   statusCodes={
     *     200="OK",
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     404="Announcement not found"
     * })
     *
     * @param int $id
     * @throws NotFoundHttpException
     * @return JsonResponse
     */
    public function getAnnouncementPicturesAction(int $id) {
        $this->get("logger")->info(sprintf("Get all pictures of an existing announcement [id: %d]", $id),
            [ 'id' => $id]);
        
        /** @var Announcement */
        $announcement = $this->get('coloc_matching.core.announcement_manager')->read($id);
        $restData = new RestDataResponse($announcement->getPictures(), "/rest/announcements/$id/pictures");
        
        $this->get("logger")->info("One announcement found", [ "response" => $restData]);
        
        return new JsonResponse($this->get("jms_serializer")->serialize($restData, "json"), Response::HTTP_OK, [ ], true);
    }


    /**
     * Get a picture of an existing announcement
     *
     * @Rest\Get("/{id}/pictures/{pictureId}", name="rest_get_announcement_picture")
     * @ApiDoc(
     *   section="Announcements",
     *   description="Get a picture of an existing announcement",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The announcement id" },
     *     { "name"="pictureId", "dataType"="Integer", "requirement"="\d+", "description"="The id of the picture" }
     *   },
     *   output={ "class"=AnnouncementPicture::class },
     *   statusCodes={
     *     200="OK",
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     404="Announcement not found or AnnouncementPicture not found"
     * })
     *
     * @param int $id
     * @param int $pictureId
     * @throws NotFoundHttpException
     * @return JsonResponse
     */
    public function getAnnouncementPictureAction(int $id, int $pictureId) {
        $this->get("logger")->info(
            sprintf("Get one picture of an existing announcement [id: %d, pictureId: %d]", $id, $pictureId),
            [ 'id' => $id, "pictureId" => $pictureId]);
        
        /** @var AnnouncementPicture */
        $picture = $this->getAnnouncementPicture($id, $pictureId);
        $restData = new RestDataResponse($picture, "/announcements/$id/pictures/$pictureId");
        
        $this->get("logger")->info(sprintf("One AnnouncementPicture found [picture: %s]", $picture),
            [ "response" => $restData]);
        
        return new JsonResponse($this->get("jms_serializer")->serialize($restData, "json"), Response::HTTP_OK, [ ], true);
    }


    /**
     * Upload a new picture for an existing Announcement
     *
     * @Rest\Post("/{id}/pictures", name="rest_upload_announcement_picture")
     * @Rest\FileParam(name="file", image=true, nullable=false, description="The picture to upload")
     * @ApiDoc(
     *   section="Announcements",
     *   description="Upload a new picture for an existing Announcement",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The announcement id" }
     *   },
     *   input={ "class"=FileType::class },
     *   statusCodes={
     *     201="Created",
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     404="Announcement not found"
     * })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function uploadNewAnnouncementPicture(int $id, Request $request) {
        $this->get("logger")->info(sprintf("Upload a new picture for an Announcement [id: %d]", $id), [
            'id' => $id]);
        
        /** @var AnnouncementManager */
        $manager = $this->get('coloc_matching.core.announcement_manager');
        /** @var Announcement */
        $announcement = $manager->read($id);
        /** @var File */
        $file = $request->files->get("file");
        
        try {
            $announcement = $manager->uploadAnnouncementPicture($announcement, $file);
            $restData = new RestDataResponse($announcement->getPictures(), "/announcements/$id/pictures");
            
            $this->get("logger")->info(sprintf("Announcement picture uploaded"), [ "response" => $restData]);
            
            return new JsonResponse($this->get("jms_serializer")->serialize($restData, "json"), Response::HTTP_CREATED,
                [ "Location" => $request->getUri()], true);
        }
        catch (InvalidFormDataException $e) {
            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ "Location" => $request->getUri()],
                true);
        }
    }


    /**
     * Delete a picture from an existing Announcement
     *
     * @Rest\Delete("/{id}/pictures/{pictureId}", name="rest_delete_announcement_picture")
     * @ApiDoc(
     *   section="Announcements",
     *   description="Upload a new picture for an existing Announcement",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The announcement id" },
     *     { "name"="pictureId", "dataType"="Integer", "requirement"="\d+", "description"="The id of the picture to delete" }
     *   },
     *   statusCodes={
     *     200="OK",
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     404="Announcement not found or AnnouncementPicture not found"
     * })
     *
     * @param int $announcementId
     * @param int $pictureId
     */
    public function deleteAnnouncementPictureAction(int $id, int $pictureId) {
        $this->get("logger")->info(
            sprintf("Delete a picture of an existing announcement [id: %d, pictureId: %d]", $id, $pictureId));
        
        /** @var AnnouncementPicture */
        $picture = $this->getAnnouncementPicture($id, $pictureId);
        
        if (!empty($picture)) {
            $this->get("logger")->info(sprintf("AnnouncementPicture found"), [ 'id' => $id,
                "pictureId" => $pictureId]);
            
            $this->get("coloc_matching.core.announcement_manager")->deleteAnnouncementPicture($picture);
        }
        
        return new JsonResponse("AnnouncementPicture deleted", Response::HTTP_OK, [ ], true);
    }


    /**
     * @Rest\Get("/{id}/candidates", name="rest_get_announcement_candidates")
     * @ApiDoc(
     *   section="Announcements",
     *   resource=true,
     *   description="Get all candidates of an existing announcement",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The announcement id" }
     *   },
     *   output={ "class"=User::class, "collection"=true },
     *   statusCodes={
     *     200="OK",
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     404="Announcement not found"
     * })
     *
     * @param int $id
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function getCandidatesAction(int $id) {
        $this->get("logger")->info(sprintf("Get all candidates of an existing announcement [id: %d]", $id),
            [ "id" => $id]);
        
        /** @var Announcement */
        $announcement = $this->get("coloc_matching.core.announcement_manager")->read($id);
        $restData = new RestDataResponse($announcement->getCandidates(), "/announcements/$id/candidates");
        
        return new JsonResponse($this->get("jms_serializer")->serialize($restData, "json"), Response::HTTP_OK, [ ], true);
    }


    /**
     * @Rest\Post("/{id}/candidates", name="rest_add_announcement_candidate")
     * @ApiDoc(
     *   section="Announcements",
     *   description="Add a candidate to an existing announcement",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The announcement id" }
     *   },
     *   output={ "class"=User::class, "collection"=true },
     *   statusCodes={
     *     201="Candidate created",
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     404="Announcement not found",
     *     422="Cannot make creator a candidate of the announcement"
     * })
     *
     * @param int $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addNewCandidateAction(int $id, Request $request) {
        $this->get("logger")->info(sprintf("Add a new candidate to an existing announcement [id: %d]", $id),
            [ "id" => $id]);
        
        /** @var AnnouncementManager */
        $manager = $this->get("coloc_matching.core.announcement_manager");
        
        /** @var Announcement */
        $announcement = $manager->read($id);
        /** @var User */
        $user = $this->extractUser($request);
        
        $announcement = $manager->addNewCandidate($announcement, $user);
        $restData = new RestDataResponse($announcement->getCandidates(), "/announcements/$id/candidates");
        
        return new JsonResponse($this->get("jms_serializer")->serialize($restData, "json"), Response::HTTP_CREATED,
            [ "Location" => $request->getUri()], true);
    }


    /**
     * @Rest\Delete("/{id}/candidates/{userId}", name="rest_remove_announcement_candidate")
     * @ApiDoc(
     *   section="Announcements",
     *   description="Remove a candidate from an existing announcement",
     *   requirements={
     *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The announcement id" },
     *     { "name"="userId", "dataType"="Integer", "requirement"="\d+", "description"="The candidate id" }
     *   },
     *   output={ "class"=User::class, "collection"=true },
     *   statusCodes={
     *     200="Candidate created",
     *     401="Unauthorized access",
     *     403="Forbidden access",
     *     404="Announcement not found"
     * })
     *
     * @param int $id
     * @param int $userId
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function removeCandidateAction(int $id, int $userId) {
        $this->get("logger")->info(sprintf("Remove a candidate from an existing announcement [id: %d]", $id),
            [ "id" => $id]);
        
        /** @var AnnouncementManager */
        $manager = $this->get("coloc_matching.core.announcement_manager");
        
        /** @var Announcement */
        $announcement = $manager->read($id);
        
        $announcement = $manager->removeCandidate($announcement, $userId);
        $restData = new RestDataResponse($announcement->getCandidates(), "/announcements/$id/candidates");
        
        return new JsonResponse($this->get("jms_serializer")->serialize($restData, "json"), Response::HTTP_OK, [ ], true);
    }


    /**
     * Get Address from string
     *
     * @param string $address
     * @throws InvalidFormDataException
     * @return Address|NULL
     */
    private function getAddress(string $address = null) {
        /** @var AddressType */
        $addressForm = $this->createForm(AddressType::class);
        
        if (!$addressForm->submit($address)->isValid()) {
            $this->get("logger")->error(sprintf("Invalid address value [address: '%s']", $address),
                [ "address" => $address, "form" => $addressForm]);
            
            throw new InvalidFormDataException("Invalid address value submitted", $addressForm->getErrors(true, true));
        }
        
        return $addressForm->getData();
    }


    private function handleUpdateRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var AnnouncementManager */
        $manager = $this->get("coloc_matching.core.announcement_manager");
        
        /** @var Announcement */
        $announcement = $manager->read($id);
        /** @var array */
        $data = $request->get("announcement", [ ]);
        
        try {
            if ($fullUpdate) {
                $announcement = $manager->update($announcement, $data);
            }
            else {
                $announcement = $manager->partialUpdate($announcement, $data);
            }
            
            $restData = new RestDataResponse($announcement, "/rest/announcement/$id");
            
            $this->get("logger")->info(sprintf("Announcement updated [announcement: %s]", $announcement),
                [ "response" => $restData]);
            
            return new JsonResponse($this->get("jms_serializer")->serialize($restData, "json"), Response::HTTP_OK,
                [ "Location" => $request->getUri()], true);
        }
        catch (InvalidFormDataException $e) {
            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ "Location" => $request->getUri()],
                true);
        }
    }


    /**
     * Get an AnnouncementPicture of an existing Announcement
     *
     * @param int $id The Id of the Announcement
     * @param int $pictureId The Id of the AnnouncementPicture to get
     * @throws NotFoundHttpException
     * @return AnnouncementPicture
     * @throws NotFoundHttpException
     */
    private function getAnnouncementPicture(int $id, int $pictureId) {
        /** @var Announcement */
        $announcement = $this->get('coloc_matching.core.announcement_manager')->read($id);
        /** @var ArrayCollection */
        $pictures = $announcement->getPictures();
        
        foreach ($pictures as $picture) {
            if ($picture->getId() == $pictureId) {
                return $picture;
            }
        }
        
        if (empty($picture)) {
            $this->get("logger")->error(sprintf("No AnnouncementPicture found with the id %d", $pictureId),
                [ 'id' => $id, "pictureId" => $pictureId]);
            
            throw new NotFoundHttpException("No AnnouncementPicture found with the Id $pictureId");
        }
    }


    /**
     * Extract the User from the authentication token in the request
     *
     * @param Request $request
     * @return \ColocMatching\CoreBundle\Entity\User\User|NULL
     * @throws JWTDecodeFailureException
     */
    private function extractUser(Request $request) {
        /** @var string */
        $token = $this->get("lexik_jwt_authentication.extractor.authorization_header_extractor")->extract($request);
        /** @var array */
        $payload = $this->get("lexik_jwt_authentication.encoder")->decode($token);
        
        return $this->get("coloc_matching.core.user_manager")->getByUsername($payload["username"]);
    }

}