<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Controller\Rest\RestDataResponse;
use ColocMatching\CoreBundle\Controller\Rest\RestListResponse;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Announcement\AnnouncementFilterType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManager;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * Lists announcements or fields with pagination
     *
     * @Rest\Get("", name="rest_get_announcements")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+", default=RequestConstants::DEFAULT_PAGE)
     * @Rest\QueryParam(name="limit", nullable=true, description="The number of results to return", requirements="\d+", default=RequestConstants::DEFAULT_LIMIT)
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results", default=RequestConstants::DEFAULT_SORT)
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$", default=RequestConstants::DEFAULT_ORDER)
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return for each result")
     *
     * @param Request $paramFetcher
     * @return JsonResponse
     */
    public function getAnnouncementsAction(ParamFetcher $paramFetcher) {
        $page = $paramFetcher->get("page", true);
        $limit = $paramFetcher->get("limit", true);
        $order = $paramFetcher->get("order", true);
        $sort = $paramFetcher->get("sort", true);
        $fields = $paramFetcher->get("fields");

        $this->get("logger")->info(
            sprintf("Get Announcements [page: %d, limit: %d, order: '%s', sort: '%s', fields: [%s]]", $page, $limit,
                $order, $sort, $fields), [ 'request' => $paramFetcher]);

        /** @var AnnouncementManager */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        /** @var AbstractFilter */
        $filter = new AnnouncementFilter();
        $filter->setPage($page)->setSize($limit)->setOrder($order)->setSort($sort);

        /** @var array */
        $announcements = empty($fields) ? $manager->list($filter) : $manager->list($filter, explode(",", $fields));
        $restList = new RestListResponse($announcements, $this->get("request_stack")->getCurrentRequest()->getUri());
        $restList->setTotal($manager->countAll())->setStart($filter->getOffset())->setOrder($order)->setSort($sort);
        $restList->setRelationLinks($page);

        /** @var int */
        $codeStatus = ($restList->getSize() < $restList->getTotal()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK;

        $this->get("logger")->info(
            sprintf("Result information : [start: %d | size: %d | total: %d]", $restList->getStart(),
                $restList->getSize(), $restList->getTotal()), [ 'response' => $restList]);

        return new JsonResponse($this->get("jms_serializer")->serialize($restList, "json"), $codeStatus, [ ], true);
    }


    /**
     * Create a new announcement for the authenticated user
     *
     * @Rest\Post("", name="rest_create_announcement")
     *
     * @param Request $request
     * @return JsonResponse
     * @throws JWTDecodeFailureException
     * @throws UnprocessableEntityHttpException
     */
    public function createAnnouncementAction(Request $request) {
        /** @var array */
        $postData = $request->request->all();
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
     * Gets an existing announcement or its fields
     *
     * @Rest\Get("/{id}", name="rest_get_announcement")
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function getAnnouncementAction(int $id, ParamFetcher $paramFetcher) {
        /** @var array */
        $fields = $paramFetcher->get("fields");

        $this->get("logger")->info(sprintf("Get an announcement by id [id: %d | fields: [%s]]", $id, $fields),
            [ "id" => $id, "paramFetcher" => $paramFetcher]);

        /** @var UserManager */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        /** @var Announcement */
        $announcement = (!$fields) ? $manager->read($id) : $manager->read($id, explode(',', $fields));
        $restData = new RestDataResponse($announcement, "/rest/announcements/$id");

        $this->get("logger")->info("One announcement found", [ "response" => $restData]);

        return new JsonResponse($this->get("jms_serializer")->serialize($restData, "json"), Response::HTTP_OK, [ ], true);
    }


    /**
     * Updates an existing announcement
     *
     * @Rest\Put("/{id}", name="rest_update_announcement")
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
     * Updates (partial) an existing announcement
     *
     * @Rest\Patch("/{id}", name="rest_patch_announcement")
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
     * Deletes an existing announcement
     *
     * @Rest\Delete("/{id}", name="rest_delete_announcement")
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

        return new JsonResponse("Announcement deleted", Response::HTTP_OK);
    }


    /**
     * Searches announcements by criteria
     *
     * @Rest\Post("/searches/", name="rest_search_announcements")
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidFormDataException
     */
    public function searchAnnouncementsAction(Request $request) {
        /** @var array */
        $filterData = $request->request->all();

        $this->get("logger")->info("Search announcements by filter",
            [ "filterData" => $filterData, "request" => $request]);

        /** @var AnnouncementManager */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        try {
            /** @var AnnouncementFilter */
            $filter = $this->buildAnnouncementFilter($filterData);

            /** @var array*/
            $announcements = $manager->search($filter);
            $restList = new RestListResponse($announcements, "/rest/announcements/searches/");
            $restList->setTotal($manager->countBy($filter))->setStart($filter->getOffset())->setOrder(
                $filter->getOrder())->setSort($filter->getSort());
            $restList->setRelationLinks($filter->getPage());

            /** @var int */
            $codeStatus = ($restList->getSize() < $restList->getTotal()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK;

            $this->get("logger")->info(
                sprintf("Result information [start: %d | size: %d | total: %d]", $restList->getStart(),
                    $restList->getSize(), $restList->getTotal()), [ 'response' => $restList, "filter" => $filter]);

            return new JsonResponse($this->get("jms_serializer")->serialize($restList, "json"), $codeStatus,
                [ "Location" => $request->getUri()], true);
        }
        catch (InvalidFormDataException $e) {
            return new JsonResponse($e->toJSON(), Response::HTTP_BAD_REQUEST, [ "Location" => $request->getUri()],
                true);
        }
    }


    /**
     * Gets all pictures of an existing announcement
     *
     * @Rest\Get("/{id}/pictures/", name="rest_get_announcement_pictures")
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
        $restData = new RestDataResponse($announcement->getPictures(), "/rest/announcements/$id/pictures/");

        $this->get("logger")->info("One announcement found", [ "response" => $restData]);

        return new JsonResponse($this->get("jms_serializer")->serialize($restData, "json"), Response::HTTP_OK, [ ], true);
    }


    /**
     * Uploads a new picture for an existing announcement
     *
     * @Rest\Post("/{id}/pictures/", name="rest_upload_announcement_picture")
     * @Rest\FileParam(name="file", image=true, nullable=false, description="The picture to upload")
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function uploadNewAnnouncementPicture(int $id, Request $request) {
        $this->get("logger")->info(sprintf("Upload a new picture for an Announcement [id: %d]", $id),
            [ 'id' => $id]);

        /** @var AnnouncementManager */
        $manager = $this->get('coloc_matching.core.announcement_manager');
        /** @var Announcement */
        $announcement = $manager->read($id);
        /** @var File */
        $file = $request->files->get("file");

        try {
            $announcement = $manager->uploadAnnouncementPicture($announcement, $file);
            $restData = new RestDataResponse($announcement->getPictures(), "/announcements/$id/pictures/");

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
     * Gets a picture of an existing announcement
     *
     * @Rest\Get("/{id}/pictures/{pictureId}", name="rest_get_announcement_picture")
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
     * Deletes a picture from an existing announcement
     *
     * @Rest\Delete("/{id}/pictures/{pictureId}", name="rest_delete_announcement_picture")
     *
     * @param int $announcementId
     * @param int $pictureId
     */
    public function deleteAnnouncementPictureAction(int $id, int $pictureId) {
        $this->get("logger")->info(
            sprintf("Delete a picture of an existing announcement [id: %d, pictureId: %d]", $id, $pictureId));

        try {
            /** @var AnnouncementPicture */
            $picture = $this->getAnnouncementPicture($id, $pictureId);

            if (!empty($picture)) {
                $this->get("logger")->info(sprintf("AnnouncementPicture found"),
                    [ 'id' => $id, "pictureId" => $pictureId]);

                $this->get("coloc_matching.core.announcement_manager")->deleteAnnouncementPicture($picture);
            }
        }
        catch (NotFoundHttpException $e) {
            // Nothing to do
        }

        return new JsonResponse("AnnouncementPicture deleted", Response::HTTP_OK);
    }


    /**
     * Gets all candidates of an existing announcement
     *
     * @Rest\Get("/{id}/candidates/", name="rest_get_announcement_candidates")
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
     * Adds the authenticated User as a candidate to an existing announcement
     *
     * @Rest\Post("/{id}/candidates/", name="rest_add_announcement_candidate")
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
     * Removes a candidate from an existing announcement
     *
     * @Rest\Delete("/{id}/candidates/{userId}", name="rest_remove_announcement_candidate")
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
     * Create an AnnouncementFilter from data array
     *
     * @param array $filterData
     * @return AnnouncementFilter
     * @throws InvalidFormDataException
     */
    private function buildAnnouncementFilter(array $filterData): AnnouncementFilter {
        /** @var AnnouncementFilterType */
        $filterForm = $this->createForm(AnnouncementFilterType::class, new AnnouncementFilter());

        if (!$filterForm->submit($filterData)->isValid()) {
            $this->get("logger")->error("Invalid filter value", [ "filterData" => $filterData,
                "form" => $filterForm]);

            throw new InvalidFormDataException("Invalid filter data submitted", $filterForm->getErrors(true, true));
        }

        return $filterForm->getData();
    }


    private function handleUpdateRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var AnnouncementManager */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        /** @var Announcement */
        $announcement = $manager->read($id);
        /** @var array */
        $data = $request->request->all();

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

        return $this->get("coloc_matching.core.user_manager")->findByUsername($payload["username"]);
    }

}