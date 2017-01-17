<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Controller\Rest\RestDataResponse;
use ColocMatching\CoreBundle\Controller\Rest\RestListResponse;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Announcement\AnnouncementType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManager;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use FOS\RestBundle\Controller\Annotations as Rest;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
	 * Get announcements or fields with pagination
	 *
	 * @Rest\Get("", name="rest_get_announcements")
	 * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+", default="1")
	 * @Rest\QueryParam(name="limit", nullable=true, description="The number of results to return", requirements="\d+", default="20")
	 * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results", default="id")
	 * @Rest\QueryParam(name="order", nullable=true, description="'asc' if ascending order, 'desc' if descending order", requirements="(asc|desc)", default="asc")
	 * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return for each result")
	 * @ApiDoc(
	 *   section="Announcements",
	 *   description="Get announcements or fields with pagination",
	 *   resource=true,
	 *   statusCodes={
	 * 	   200="OK",
	 *     206="Partial content"
	 *   },
	 *   output={ "class"=Announcement::class, "collection"=true }
	 * )
	 *
	 * @param Request $request
	 * @return JsonResponse
	 * @throws NotFoundHttpException
	 */
	public function getAnnouncementsAction(Request $request) {
		$page = $request->query->get('page', RequestConstants::DEFAULT_PAGE);
		$limit = $request->query->get('limit', RequestConstants::DEFAULT_LIMIT);
		$order = $request->query->get('order', RequestConstants::DEFAULT_ORDER);
		$sort = $request->query->get('sort', RequestConstants::DEFAULT_SORT);
		$fields = $request->query->get('fields', null);
		
		$this->get('logger')->info(
			sprintf("Get Announcements [page: %d | limit: %d | order: '%s' | sort: '%s' | fields: [%s]]",
					$page, $limit, $order, $sort, $fields),
			['request' => $request]);
		
		/** @var array */
		$announcements = [];
		/** @var AnnouncementManager */
		$manager = $this->get("coloc_matching.core.announcement_manager");
		
		/** @var AbstractFilter */
		$filter = new AnnouncementFilter();
		$filter
			->setOffset(($page-1) * $limit)
			->setSize($limit)
			->setOrder($order)
			->setSort($sort);
		
		if ($fields) {
			$announcements = $manager->getFields($fields, $filter);
		} else {
			$announcements = $manager->getAll($filter);
		}
		
		$restList = new RestListResponse($announcements, "/rest/announcements");
		$restList
			->setTotal($manager->countAll())
			->setStart(($page-1) * $limit)
			->setOrder($order)
			->setSort($sort);
		$restList->setRelationLinks($page);
		
		/** @var int */
		$codeStatus = ($restList->getSize() < $restList->getTotal()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK;
		
		$this->get('logger')->info(
			sprintf("Result information : [start: %d | size: %d | total: %d]",
				$restList->getStart(), $restList->getSize(), $restList->getTotal()),
			['response' => $restList]);
		
		return new JsonResponse(
			$this->get('jms_serializer')->serialize($restList, 'json'),
			$codeStatus, ["Location" => $request->getUri()], true);
	}
	
	
	/**
	 * Get an announcement or its fields by id
	 *
	 * @Rest\Get("/{id}", name="rest_get_announcement")
	 * @Rest\QueryParam(name="fields", nullable=true, description="The fields to return")
	 * @ApiDoc(
	 *   section="Announcements",
	 *   description="Get an announcement or its fields by id",
	 *   requirements={
	 *     { "name"="id", "dataType"="Integer", "requirement"="\d+", "description"="The announcement id" }
	 *   },
	 *   output="ColocMatching\CoreBundle\Entity\Announcement\Announcement",
	 *   statusCodes={
	 *     200="OK",
	 *     401="Unauthorized access",
	 *     403="Forbidden access",
	 *     404="Announcement not found"
	 * })
	 *
	 * @param int $id
	 * @return JsonResponse
	 */
	public function getAnnouncementAction(int $id, Request $request) {
		/** @var array */
		$fields = $request->query->get('fields', null);
		
		$this->get('logger')->info(
			sprintf("Get an announcement by id [id=%d | fields=[%s]]", $id, $fields),
			['id' => $id, 'request' => $request]);
		
		/** @var UserManager */
		$manager = $this->get('coloc_matching.core.announcement_manager');
		
		/** @var Announcement */
		$announcement = (!$fields) ? $manager->getById($id) : $manager->getFieldsById($id, explode(',', $fields));
		
		if (!$announcement) {
			$this->get('logger')->error(
				sprintf("No Announcement found with the id %d", $id),
				['id' => $id, 'request' => $request]);
				
			throw new NotFoundHttpException("User not found with the Id $id");
		}
		
		$restData = new RestDataResponse($announcement, "/rest/announcements/$id");
		
		$this->get('logger')->info("One announcement found", ["response" => $restData]);
		
		
		return new JsonResponse($this->get('jms_serializer')->serialize($restData, 'json'),
			Response::HTTP_OK, ["Location" => $request->getUri()], true);
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
	 *     403="Forbidden access"
	 *   },
	 *   responseMap={
	 *     201={ "class"=Announcement::class },
	 *     400={ "class"=AnnouncementType::class, "form_errors"=true, "name"="" }
	 * })
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function createAnnouncementAction(Request $request) {
		/** @var array */
		$postData = $request->get("announcement", []);
		/** @var User */
		$user = $this->extractUser($request);
		
		$this->get('logger')->info(sprintf("Post a new Announcement"), array (
			"user" => $user,
			"request" => $request));
		
		if (!empty($user->getAnnouncement())) {
			$this->get('logger')->error(
				sprintf("This user already has an Announcement"), array (
				"user" => $user,
				"announcement" => $user->getAnnouncement()));
				
			throw new UnprocessableEntityHttpException("This user already has an Announcement");
		}
		
		try {
			/** @var Announcement */
			$announcement = $this->get('coloc_matching.core.announcement_manager')->create($user, $postData);
		
			$restData = new RestDataResponse($announcement, '/rest/announcements/' . $announcement->getId());
			$responseData = $this->get('jms_serializer')->serialize($restData, 'json');
			$statusCode = Response::HTTP_CREATED;
				
			$this->get('logger')->info(
				sprintf("Announcement created [announcement: %s]", $announcement),
				['response' => $responseData]);
		} catch (InvalidFormDataException $e) {
			$this->get('logger')->error(
				sprintf("Error while trying to create a new Announcement"),
				['exception' => $e]);
				
			$responseData = $e->toJSON();
			$statusCode = Response::HTTP_BAD_REQUEST;
		}
		
		return new JsonResponse($responseData, $statusCode, ["Location" => $request->getUri()], true);
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
	 *     400="Invalid form",
	 *     401="Unauthorized access",
	 *     403="Forbidden access",
	 *     404="Announcement not found"
	 *
	 *   },
	 *   responseMap={
	 *     200={ "class"=Announcement::class },
	 *     400={ "class"=AnnouncementType::class, "form_errors"=true, "name"="" }
	 * })
	 *
	 * @param int $id
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function updateAnnouncementAction(int $id, Request $request) {
		$this->get('logger')->info(
			sprintf("Put an announcement with the following id [id: %d]", $id),
			['id' => $id, 'request' => $request]);
		
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
	 */
	public function patchAnnouncementAction(int $id, Request $request) {
		$this->get('logger')->info(
				sprintf("Patch an announcement with the following id [id: %d]", $id),
				['id' => $id, 'request' => $request]);
		
		return $this->handleUpdateRequest($id, $request, false);
	}
	
	
	/**
	 * Patch an existing announcement
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
		
		$this->get('logger')->info(
				sprintf("Delete an announcement with the following id [id: %d]", $id),
				['id' => $id]);
		
		/** @var Announcement */
		$announcement = $manager->getById($id);
		
		if ($announcement) {
			$this->get('logger')->info(sprintf("Announcement found [announcement: %s]", $announcement));
				
			$manager->delete($announcement);
		}
		
		return new JsonResponse("Announcement deleted", Response::HTTP_OK, [], true);
	}
	
	
	private function handleUpdateRequest(int $id, Request $request, bool $fullUpdate) {
		/** @var AnnouncementManager */
		$manager = $this->get("coloc_matching.core.announcement_manager");
		/** @var Announcement */
		$announcement = $manager->getById($id);
		
		if (!$announcement) {
			$this->get("logger")->error(
				sprintf("No announcement found [id: %d]", $id),
				["id" => $id, "request" => $request]);
			
			throw new NotFoundHttpException("No announcement found with the Id $id");
		}
		
		/** @var array */
		$data = $request->get("announcement", []);
		
		try {
			if ($fullUpdate) {
				$announcement = $manager->update($announcement, $data);
			} else {
				$announcement = $manager->partialUpdate($announcement, $data);
			}
			
			$restData = new RestDataResponse($announcement, "/rest/announcement/$id");
			$responseData = $this->get("jms_serializer")->serialize($restData, "json");
			$statusCode = Response::HTTP_OK;
			
			$this->get("logger")->info(
				sprintf("Announcement updated [announcement: %s]", $announcement),
				["response" => $responseData]);
		} catch (InvalidFormDataException $e) {
			$this->get('logger')->error(
				sprintf("Error while trying to update an announcement [id: %d]", $id),
				['exception' => $e]);
			
			$responseData = $e->toJSON();
			$statusCode = Response::HTTP_BAD_REQUEST;
		}
		
		return new JsonResponse($responseData, $statusCode, ["Location" => $request->getUri()], true);
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