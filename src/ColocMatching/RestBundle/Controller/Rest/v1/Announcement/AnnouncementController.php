<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Filter\AnnouncementFilterType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\RestBundle\Controller\Response\EntityResponse;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\Announcement\AnnouncementControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
class AnnouncementController extends RestController implements AnnouncementControllerInterface {

    /**
     * Lists announcements or fields with pagination
     *
     * @Rest\Get("", name="rest_get_announcements")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="20")
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results",
     *   default="id")
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$",
     *   default="asc")
     * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return for each result")
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getAnnouncementsAction(ParamFetcher $paramFetcher) {
        $pageable = $this->extractPageableParameters($paramFetcher);
        $fields = $paramFetcher->get("fields");

        $this->get("logger")->info("Listing announcements", array ("pagination" => $pageable, "fields" => $fields));

        /** @var PageableFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($pageable["page"],
            $pageable["size"], $pageable["order"], $pageable["sort"]);
        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");
        /** @var array */
        $announcements = empty($fields) ? $manager->list($filter) : $manager->list($filter, explode(",", $fields));
        /** @var PageResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($announcements,
            $manager->countAll(), $filter);

        $this->get("logger")->info("Listing announcements - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Create a new announcement for the authenticated user
     *
     * @Rest\Post("", name="rest_create_announcement")
     * @Security(expression="has_role('ROLE_PROPOSAL')")
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JWTDecodeFailureException
     * @throws UnprocessableEntityHttpException
     */
    public function createAnnouncementAction(Request $request) {
        /** @var User */
        $user = $this->extractUser($request);

        $this->get("logger")->info("Posting a new announcement",
            array ("user" => $user, "request" => $request->request));

        try {
            /** @var Announcement */
            $announcement = $this->get('coloc_matching.core.announcement_manager')->create($user,
                $request->request->all());
            /** @var string */
            $url = sprintf("%s/%d", $request->getUri(), $announcement->getId());
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($announcement, $url);

            $this->get("logger")->info("Announcement created", array ("response" => $response));

            return $this->buildJsonResponse($response,
                Response::HTTP_CREATED, array ("Location" => $url));
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to create an announcement",
                array ("request" => $request->request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
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
     *
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
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($announcement);

        $this->get("logger")->info("One announcement found", array ("id" => $id, "response" => $response));

        if ($announcement instanceof Visitable) {
            $this->registerVisit($announcement);
        }

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Updates an existing announcement
     *
     * @Rest\Put("/{id}", name="rest_update_announcement")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function updateAnnouncementAction(int $id, Request $request) {
        $this->get("logger")->info("Putting an existing announcement",
            array ("id" => $id, "request" => $request->request));

        return $this->handleUpdateAnnouncementRequest($id, $request, true);
    }


    /**
     * Updates (partial) an existing announcement
     *
     * @Rest\Patch("/{id}", name="rest_patch_announcement")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function patchAnnouncementAction(int $id, Request $request) {
        $this->get("logger")->info("Patching an existing announcement",
            array ("id" => $id, "request" => $request->request));

        return $this->handleUpdateAnnouncementRequest($id, $request, false);
    }


    /**
     * Deletes an existing announcement
     *
     * @Rest\Delete("/{id}", name="rest_delete_announcement")
     *
     * @param int $id
     *
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
     *
     * @return JsonResponse
     * @throws InvalidFormDataException
     */
    public function searchAnnouncementsAction(Request $request) {
        $this->get("logger")->info("Searching announcements by filter", array ("request" => $request->request));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        try {
            /** @var AnnouncementFilter $filter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(
                AnnouncementFilterType::class, new AnnouncementFilter(), $request->request->all());
            /** @var array */
            $announcements = $manager->search($filter);
            /** @var PageResponse */
            $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($announcements,
                $manager->countBy($filter), $filter);

            $this->get("logger")->info("Searching announcements by filter - result information",
                array ("filter" => $filter, "response" => $response));

            return $this->buildJsonResponse($response,
                ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to search announcements",
                array ("request" => $request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }


    /**
     * Gets the location of an existing announcement
     *
     * @Rest\Get("/{id}/location", name="rest_get_announcement_location")
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function getAnnouncementLocationAction(int $id) {
        $this->get("logger")->info("Getting the location of an existing announcement", array ("id" => $id));

        /** @var Announcement */
        $announcement = $this->get("coloc_matching.core.announcement_manager")->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse(
            $announcement->getLocation());

        $this->get("logger")->info("One announcement found", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Gets all candidates of an existing announcement
     *
     * @Rest\Get("/{id}/candidates", name="rest_get_announcement_candidates")
     * @Security(expression="has_role('ROLE_USER')")
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AnnouncementNotFoundException
     */
    public function getCandidatesAction(int $id) {
        $this->get("logger")->info("Getting all candidates of an existing announcement", array ("id" => $id));

        /** @var Announcement */
        $announcement = $this->get("coloc_matching.core.announcement_manager")->read($id);
        /** @var EntityResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse(
            $announcement->getCandidates());

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Removes a candidate from an existing announcement
     *
     * @Rest\Delete("/{id}/candidates/{userId}", name="rest_remove_announcement_candidate")
     *
     * @param int $id
     * @param int $userId
     *
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


    private function handleUpdateAnnouncementRequest(int $id, Request $request, bool $fullUpdate) {
        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        /** @var Announcement */
        $announcement = $manager->read($id);

        try {
            $announcement = $manager->update($announcement, $request->request->all(), $fullUpdate);
            /** @var EntityResponse */
            $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($announcement);

            $this->get("logger")->info("Announcement updated", array ("response" => $response));

            return $this->buildJsonResponse($response, Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update an announcement",
                array ("id" => $id, "request" => $request->request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }

}