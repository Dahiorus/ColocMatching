<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Controller\Response\EntityResponse;
use ColocMatching\CoreBundle\Controller\Response\PageResponse;
use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\AnnouncementControllerInterface;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\AnnouncementPictureNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Filter\AnnouncementFilterType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Doctrine\Common\Collections\Collection;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * REST controller for resource /announcements
 *
 * @Rest\Route("/announcements")
 *
 * @author brondon.ung
 */
class AnnouncementController extends Controller implements AnnouncementControllerInterface {


    /**
     * Lists announcements or fields with pagination
     *
     * @Rest\Get("", name="rest_get_announcements")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+", default=RequestConstants::DEFAULT_PAGE)
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+", default=RequestConstants::DEFAULT_LIMIT)
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results", default=RequestConstants::DEFAULT_SORT)
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$", default=RequestConstants::DEFAULT_ORDER)
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return for each result")
     *
     * @param Request $paramFetcher
     * @return JsonResponse
     */
    public function getAnnouncementsAction(ParamFetcher $paramFetcher) {
        $page = $paramFetcher->get("page", true);
        $limit = $paramFetcher->get("size", true);
        $order = $paramFetcher->get("order", true);
        $sort = $paramFetcher->get("sort", true);
        $fields = $paramFetcher->get("fields");

        $this->get("logger")->info("Listing announcements",
            array ("page" => $page, "size" => $limit, "order" => $order, "sort" => $sort, "fields" => $fields));

        /** @var PageableFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($page, $limit, $order, $sort);
        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");
        /** @var array */
        $announcements = empty($fields) ? $manager->list($filter) : $manager->list($filter, explode(",", $fields));
        /** @var PageResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($announcements,
            $manager->countAll(), $filter);

        $this->get("logger")->info("Listing announcements - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Create a new announcement for the authenticated user
     *
     * @Rest\Post("", name="rest_create_announcement")
     *
     * @Security(expression="has_role('ROLE_PROPOSAL')")
     *
     * @param Request $request
     * @return JsonResponse
     * @throws JWTDecodeFailureException
     * @throws UnprocessableEntityHttpException
     */
    public function createAnnouncementAction(Request $request) {
        /** @var User */
        $user = $this->get("coloc_matching.core.controller_utils")->extractUser($request);

        $this->get("logger")->info("Posting a new announcement", array ("user" => $user, "request" => $request));

        try {
            /** @var Announcement */
            $announcement = $this->get('coloc_matching.core.announcement_manager')->create($user,
                $request->request->all());
            /** @var string */
            $url = sprintf("%s/%d", $request->getUri(), $announcement->getId());
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($announcement, $url);

            $this->get("logger")->info("Announcement created", array ("response" => $response));

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response,
                Response::HTTP_CREATED, array ("Location" => $url));
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to create an announcement",
                array ("request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
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
     * @throws AnnouncementNotFoundException
     */
    public function getAnnouncementAction(int $id, ParamFetcher $paramFetcher) {
        /** @var array */
        $fields = $paramFetcher->get("fields");

        $this->get("logger")->info("Getting an existing announcement", array ("id" => $id, "fields" => $fields));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");
        /** @var Announcement */
        $announcement = (!$fields) ? $manager->read($id) : $manager->read($id, explode(',', $fields));
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($announcement);

        $this->get("logger")->info("One announcement found", array ("id" => $id, "response" => $response));

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Updates an existing announcement
     *
     * @Rest\Put("/{id}", name="rest_update_announcement")
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function updateAnnouncementAction(int $id, Request $request) {
        $this->get("logger")->info("Putting an existing announcement", array ("id" => $id, "request" => $request));

        return $this->handleUpdateAnnouncementRequest($id, $request, true);
    }


    /**
     * Updates (partial) an existing announcement
     *
     * @Rest\Patch("/{id}", name="rest_patch_announcement")
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function patchAnnouncementAction(int $id, Request $request) {
        $this->get("logger")->info("Patching an existing announcement", array ("id" => $id, "request" => $request));

        return $this->handleUpdateAnnouncementRequest($id, $request, false);
    }


    /**
     * Deletes an existing announcement
     *
     * @Rest\Delete("/{id}", name="rest_delete_announcement")
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAnnouncementAction(int $id) {
        $this->get("logger")->info("Deleting an existing announcement", array ("id" => $id));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get('coloc_matching.core.announcement_manager');

        try {
            /** @var Announcement */
            $announcement = $manager->read($id);

            if (!empty($announcement)) {
                $this->get("logger")->info("Announcement found", array ("announcement" => $announcement));

                $manager->delete($announcement);
            }
        }
        catch (AnnouncementNotFoundException $e) {
            // nothing to do
        }

        return new JsonResponse("Announcement deleted");
    }


    /**
     * Searches announcements by criteria
     *
     * @Rest\Post("/searches", name="rest_search_announcements")
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidFormDataException
     */
    public function searchAnnouncementsAction(Request $request) {
        $this->get("logger")->info("Searching announcements by filter", array ("request" => $request));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        try {
            /** @var AnnouncementFilter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(
                AnnouncementFilterType::class, new AnnouncementFilter(), $request->request->all());

            /** @var array */
            $announcements = $manager->search($filter);

            /** @var PageResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($announcements,
                $manager->countBy($filter), $filter);

            $this->get("logger")->info("Searching announcements by filter - result information",
                array ("filter" => $filter, "response" => $response));

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response,
                ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to search announcements",
                array ("request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
        }
    }


    /**
     * Gets the location of an existing announcement
     *
     * @Rest\Get("/{id}/location", name="rest_get_announcement_location")
     *
     * @param int $id
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function getAnnouncementLocationAction(int $id) {
        $this->get("logger")->info("Getting the location of an existing announcement", array ("id" => $id));

        /** @var Announcement */
        $announcement = $this->get("coloc_matching.core.announcement_manager")->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse(
            $announcement->getLocation());

        $this->get("logger")->info("One announcement found", array ("response" => $response));

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Gets all pictures of an existing announcement
     *
     * @Rest\Get("/{id}/pictures", name="rest_get_announcement_pictures")
     *
     * @param int $id
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function getAnnouncementPicturesAction(int $id) {
        $this->get("logger")->info("Getting all pictures of an existing announcement", array ("id" => $id));

        /** @var Announcement */
        $announcement = $this->get('coloc_matching.core.announcement_manager')->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createRestDataResponse(
            $announcement->getPictures());

        $this->get("logger")->info("One announcement found", array ("response" => $response));

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Uploads a new picture for an existing announcement
     *
     * @Rest\Post("/{id}/pictures", name="rest_upload_announcement_picture")
     * @Rest\FileParam(name="file", image=true, nullable=false, description="The picture to upload")
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function uploadNewAnnouncementPicture(int $id, Request $request) {
        $this->get("logger")->info("Uploading a new picture for an existing announcement", array ('id' => $id));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get('coloc_matching.core.announcement_manager');
        /** @var Announcement */
        $announcement = $manager->read($id);
        /** @var File */
        $file = $request->files->get("file");

        try {
            /** @var AnnouncementPicture */
            $picture = $manager->uploadAnnouncementPicture($announcement, $file);
            /** @var string */
            $url = sprintf("%s/%s", $request->getUri(), $picture->getId());
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($picture, $url);

            $this->get("logger")->info("Announcement picture uploaded", array ("response" => $response));

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response,
                Response::HTTP_CREATED, array ("Location" => $url));
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to upload a picture for an announcement",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
        }
    }


    /**
     * Gets a picture of an existing announcement
     *
     * @Rest\Get("/{id}/pictures/{pictureId}", name="rest_get_announcement_picture")
     *
     * @param int $id
     * @param int $pictureId
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     * @throws AnnouncementPictureNotFoundException
     */
    public function getAnnouncementPictureAction(int $id, int $pictureId) {
        $this->get("logger")->info("Getting one picture of an existing announcement",
            array ("id" => $id, "pictureId" => $pictureId));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");
        /** @var Announcement */
        $announcement = $manager->read($id);
        /** @var AnnouncementPicture */
        $picture = $manager->readAnnouncementPicture($announcement, $pictureId);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($picture);

        $this->get("logger")->info("One announcement picture found", array ("response" => $response));

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Deletes a picture from an existing announcement
     *
     * @Rest\Delete("/{id}/pictures/{pictureId}", name="rest_delete_announcement_picture")
     *
     * @param int $announcementId
     * @param int $pictureId
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function deleteAnnouncementPictureAction(int $id, int $pictureId) {
        $this->get("logger")->info("Deleting a picture of an existing announcement",
            array ("id" => $id, "pictureId" => $pictureId));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        try {
            /** @var Announcement */
            $announcement = $manager->read($id);
            /** @var AnnouncementPicture */
            $picture = $manager->readAnnouncementPicture($announcement, $pictureId);

            if (!empty($picture)) {
                $this->get("logger")->info(sprintf("AnnouncementPicture found"),
                    array ("id" => $id, "pictureId" => $pictureId));

                $this->get("coloc_matching.core.announcement_manager")->deleteAnnouncementPicture($picture);
            }
        }
        catch (AnnouncementPictureNotFoundException $e) {
            // Nothing to do
        }

        return new JsonResponse("AnnouncementPicture deleted", Response::HTTP_OK);
    }


    /**
     * Gets all candidates of an existing announcement
     *
     * @Rest\Get("/{id}/candidates", name="rest_get_announcement_candidates")
     *
     * @param int $id
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function getCandidatesAction(int $id) {
        $this->get("logger")->info("Getting all candidates of an existing announcement", array ("id" => $id));

        /** @var Announcement */
        $announcement = $this->get("coloc_matching.core.announcement_manager")->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse(
            $announcement->getCandidates());

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Adds the authenticated User as a candidate to an existing announcement
     *
     * @Rest\Post("/{id}/candidates", name="rest_add_announcement_candidate")
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function addNewCandidateAction(int $id, Request $request) {
        $this->get("logger")->info("Adding a new candidate to an existing announcement", array ("id" => $id));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        /** @var Announcement */
        $announcement = $manager->read($id);
        /** @var User */
        $user = $this->get("coloc_matching.core.controller_utils")->extractUser($request);

        /** @var Collection */
        $candidates = $manager->addCandidate($announcement, $user);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($candidates);

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_CREATED,
            array ("Location" => $request->getUri()));
    }


    /**
     * Removes a candidate from an existing announcement
     *
     * @Rest\Delete("/{id}/candidates/{userId}", name="rest_remove_announcement_candidate")
     *
     * @param int $id
     * @param int $userId
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function removeCandidateAction(int $id, int $userId) {
        $this->get("logger")->info("Removing a candidate from an existing announcement", array ("id" => $id));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        /** @var Announcement */
        $announcement = $manager->read($id);

        $manager->removeCandidate($announcement, $userId);

        return new JsonResponse("Candidate removed");
    }


    /**
     * Gets the housing of an existing announcement
     *
     * @Rest\Get("/{id}/housing", name="rest_get_announcement_housing")
     *
     * @param int $id
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

        return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Updates the housing of an existing announcement
     *
     * @Rest\Put("/{id}/housing", name="rest_update_announcement_housing")
     *
     * @param int $id
     * @param Request $request
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
     * @Rest\Patch("/{id}/housing", name="rest_patch_announcement_housing")
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function patchHousingAction(int $id, Request $request) {
        $this->get("logger")->info("Patching an announcement's housing", array ("id" => $id, "request" => $request));

        return $this->handleUpdateHousingRequest($id, $request, false);
    }


    private function handleUpdateAnnouncementRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        /** @var Announcement */
        $announcement = $manager->read($id);

        try {
            $announcement = $manager->update($announcement, $request->request->all(), $fullUpdate);
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createEntityResponse($announcement);

            $this->get("logger")->info("Announcement updated", array ("response" => $response));

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update an announcement",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
        }
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

            return $this->get("coloc_matching.core.controller_utils")->buildJsonResponse($response, Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a housing",
                array ("id" => $id, "request" => $request, "exception" => $e));

            return $this->get("coloc_matching.core.controller_utils")->buildBadRequestResponse($e);
        }
    }

}