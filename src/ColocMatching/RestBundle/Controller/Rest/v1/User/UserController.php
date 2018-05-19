<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Filter\UserFilterForm;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\PageRequest;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use ColocMatching\CoreBundle\Service\VisitorInterface;
use ColocMatching\CoreBundle\Validator\FormValidator;
use ColocMatching\RestBundle\Controller\Response\CollectionResponse;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
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
 * REST controller for resource /users
 *
 * @Rest\Route(path="/users", service="coloc_matching.rest.user_controller")
 *
 * @author Dahiorus
 */
class UserController extends AbstractRestController
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var FormValidator */
    private $formValidator;

    /** @var VisitorInterface */
    private $visitVisitor;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, UserDtoManagerInterface $userManager,
        FormValidator $formValidator, VisitorInterface $visitVisitor)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->userManager = $userManager;
        $this->formValidator = $formValidator;
        $this->visitVisitor = $visitVisitor;
    }


    /**
     * Lists users
     *
     * @Rest\Get(name="rest_get_users")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", map=true, description="Sorting parameters", requirements="\w+,(asc|desc)",
     *   default={ "createdAt,asc" }, allowBlank=false)
     *
     * @Operation(tags={ "User" },
     *   @SWG\Response(response=200, description="Users found"),
     *   @SWG\Response(response=206, description="Partial content")
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getUsersAction(ParamFetcher $paramFetcher)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->debug("Listing users", $parameters);

        $pageable = PageRequest::create($parameters);
        $response = new PageResponse(
            $this->userManager->list($pageable),
            "rest_get_users", $paramFetcher->all(),
            $pageable, $this->userManager->countAll());

        $this->logger->info("Listing users - result information",
            array ("pageable" => $pageable, "response" => $response));

        return $this->buildJsonResponse($response, ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT :
            Response::HTTP_OK);
    }


    /**
     * Gets a user
     *
     * @Rest\Get("/{id}", name="rest_get_user", requirements={"id"="\d+"})
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(response=200, description="User found", @Model(type=UserDto::class)),
     *   @SWG\Response(response=404, description="No user found")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     */
    public function getUserAction(int $id)
    {
        $this->logger->debug("Getting an existing user", array ("id" => $id));

        /** @var UserDto $user */
        $user = $this->userManager->read($id);

        $this->logger->info("One user found", array ("response" => $user));

        $this->visitVisitor->visit($user);

        return $this->buildJsonResponse($user, Response::HTTP_OK);
    }


    /**
     * Searches specific users
     *
     * @Rest\Post("/searches", name="rest_search_users")
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(name="filter", in="body", required=true, description="Criteria filter",
     *     @Model(type=UserFilterForm::class)),
     *   @SWG\Response(response=200, description="Users found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function searchUsersAction(Request $request)
    {
        $this->logger->debug("Searching specific users", array ("postParams" => $request->request->all()));

        /** @var UserFilter $filter */
        $filter = $this->formValidator->validateFilterForm(UserFilterForm::class, new UserFilter(),
            $request->request->all());
        $response = new CollectionResponse($this->userManager->search(
            $filter, $filter->getPageable()), "rest_search_users");

        $this->logger->info("Searching users by filtering - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response);
    }

}
