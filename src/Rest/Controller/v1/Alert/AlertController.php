<?php

namespace App\Rest\Controller\v1\Alert;

use App\Core\DTO\Alert\AlertDto;
use App\Core\DTO\User\UserDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Form\Type\Alert\AnnouncementAlertDtoForm;
use App\Core\Form\Type\Alert\GroupAlertDtoForm;
use App\Core\Manager\Alert\AlertDtoManagerInterface;
use App\Core\Repository\Filter\AnnouncementFilter;
use App\Core\Repository\Filter\GroupFilter;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Security\User\TokenEncoderInterface;
use App\Rest\Controller\Response\Alert\AlertPageResponse;
use App\Rest\Controller\Response\PageResponse;
use App\Rest\Controller\v1\AbstractRestController;
use App\Rest\Security\Authorization\Voter\AlertVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST Controller for the resource /alerts
 *
 * @Rest\Route(path="/alerts")
 * @Security("is_granted('ROLE_USER')")
 *
 * @author Dahiorus
 */
class AlertController extends AbstractRestController
{
    /** @var AlertDtoManagerInterface */
    private $alertManager;

    /** @var RouterInterface */
    private $router;

    /** @var TokenEncoderInterface */
    private $tokenEncoder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, AlertDtoManagerInterface $alertManager,
        RouterInterface $router, TokenEncoderInterface $tokenEncoder)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->alertManager = $alertManager;
        $this->router = $router;
        $this->tokenEncoder = $tokenEncoder;
    }


    /**
     * Lists a user's alerts
     *
     * @Rest\Get(name="rest_get_alerts")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters (prefix with '-' to DESC sort)",
     *   default="-createdAt")
     *
     * @Operation(tags={ "Alert" },
     *   @SWG\Response(response=200, description="Alerts found", @Model(type=AlertPageResponse::class)),
     *   @SWG\Response(response=206, description="Partial content"),
     *   @SWG\Response(response=401, description="Unauthorized")
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getAlertsAction(ParamFetcher $paramFetcher, Request $request)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);
        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);

        $this->logger->debug("Listing a user's alerts", array_merge(["user" => $user], $parameters));

        $pageable = PageRequest::create($parameters);
        $response = new PageResponse(
            $this->alertManager->findByUser($user, $pageable), "rest_get_alerts", $paramFetcher->all());

        $this->logger->info("Listing a user's alerts - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Creates an announcement alert
     *
     * @Rest\Post(path="/announcements", name="rest_create_announcement_alert")
     *
     * @Operation(tags={ "Alert" },
     *   @SWG\Parameter(name="alert", in="body", required=true, description="The alert to create",
     *     @Model(type=AnnouncementAlertDtoForm::class)),
     *   @SWG\Response(response=201, description="Alert created", @Model(type=AlertDto::class)),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function createAnnouncementAlertAction(Request $request)
    {
        return $this->handleCreateAlert(AnnouncementFilter::class, $request);
    }


    /**
     * Creates an group alert
     *
     * @Rest\Post(path="/groups", name="rest_create_group_alert")
     *
     * @Operation(tags={ "Alert" },
     *   @SWG\Parameter(name="alert", in="body", required=true, description="The alert to create",
     *     @Model(type=GroupAlertDtoForm::class)),
     *   @SWG\Response(response=201, description="Alert created", @Model(type=AlertDto::class)),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function createGroupAlertAction(Request $request)
    {
        return $this->handleCreateAlert(GroupFilter::class, $request);
    }


    /**
     * Gets an alert
     *
     * @Rest\Get("/{id}", name="rest_get_alert", requirements={ "id"="\d+" })
     *
     * @Operation(tags={ "Alert" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The alert identifier"),
     *   @SWG\Response(response=200, description="Alert found", @Model(type=AlertDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No alert found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function getAlertAction(int $id)
    {
        $this->logger->debug("Getting an alert", array ("id" => $id));

        /** @var AlertDto $alert */
        $alert = $this->alertManager->read($id);
        $this->evaluateUserAccess(AlertVoter::GET, $alert);

        $this->logger->info("Alert found", array ("alert" => $alert));

        return $this->buildJsonResponse($alert);
    }


    /**
     * Updates an alert
     *
     * @Rest\Put("/{id}", name="rest_update_alert", requirements={ "id"="\d+" })
     * @Rest\Patch("/{id}", name="rest_patch_alert", requirements={ "id"="\d+" })
     *
     * @Operation(tags={ "Alert" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The alert identifier"),
     *   @SWG\Parameter(name="alert", in="body", required=true, description="The alert to update",
     *     @Model(type=AnnouncementAlertDtoForm::class)),
     *   @SWG\Response(response=200, description="Alert updated"),
     *   @SWG\Response(response=400, description="Validation error"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No alert found")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function updateAlertAction(int $id, Request $request)
    {
        $this->logger->debug("Updating an alert", array ("id" => $id));

        /** @var AlertDto $alert */
        $alert = $this->alertManager->read($id);
        $this->evaluateUserAccess(AlertVoter::UPDATE, $alert);

        $updatedAlert = $this->alertManager->update($alert, $request->request->all(), $request->isMethod("PUT"));

        $this->logger->info("Alert updated", array ("response" => $updatedAlert));

        return $this->buildJsonResponse($alert);
    }


    /**
     * Deletes an alert
     *
     * @Rest\Delete("/{id}", name="rest_delete_alert", requirements={ "id"="\d+" })
     *
     * @Operation(tags={ "Alert" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The alert identifier"),
     *   @SWG\Response(response=204, description="Alert deleted"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteAlertAction(int $id)
    {
        $this->logger->debug("Deleting an alert", array ("id" => $id));

        try
        {
            /** @var AlertDto $alert */
            $alert = $this->alertManager->read($id);
            $this->evaluateUserAccess(AlertVoter::DELETE, $alert);
            $this->alertManager->delete($alert);
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->warning("Trying to delete a non existing alert", array ("id" => $id));
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    /**
     * Handles the alert creation
     *
     * @param string $filterClass Searchable filter class name
     * @param Request $request The request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    private function handleCreateAlert(string $filterClass, Request $request) : JsonResponse
    {
        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);
        $data = $request->request->all();

        $this->logger->debug("Posting a new alert", array ("user" => $user, "postParams" => $data));

        /** @var AlertDto $alert */
        $alert = $this->alertManager->create($user, $filterClass, $data);

        $this->logger->info("Alert created", array ("response" => $alert));

        return $this->buildJsonResponse($alert, Response::HTTP_CREATED, array (
            "Location" => $this->router->generate("rest_get_alert", array ("id" => $alert->getId()),
                Router::ABSOLUTE_URL)
        ));
    }

}
