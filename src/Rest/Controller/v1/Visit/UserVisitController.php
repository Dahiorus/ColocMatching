<?php

namespace App\Rest\Controller\v1\Visit;

use App\Core\Entity\User\User;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Form\Type\Filter\VisitFilterForm;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Manager\Visit\VisitDtoManagerInterface;
use App\Core\Validator\FormValidator;
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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for the resource /users/{id}/visits
 *
 * @Rest\Route(path="/users/{id}/visits")
 * @Security(expression="has_role('ROLE_USER')")
 *
 * @author Dahiorus
 */
class UserVisitController extends AbstractVisitedVisitController
{
    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, VisitDtoManagerInterface $visitManager,
        UserDtoManagerInterface $visitedManager, FormValidator $formValidator)
    {
        parent::__construct($logger, $serializer, $authorizationChecker, $visitManager, $visitedManager,
            $formValidator);
    }


    /**
     * Lists visits done on a user
     *
     * @Rest\Get(name="rest_get_user_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters", default="createdAt")
     *
     * @Operation(tags={ "Visits" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(response=200, description="Visits found"),
     *   @SWG\Response(response=206, description="Partial content"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Forbidden access")
     * )
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getVisitsAction(int $id, ParamFetcher $paramFetcher)
    {
        return parent::getVisitsAction($id, $paramFetcher);
    }


    /**
     * Searches specific visits done on a user
     *
     * @Rest\Post(path="/searches", name="rest_search_user_visits")
     *
     * @Operation(tags={ "Visits" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(name="filter", in="body", required=true, description="Criteria filter",
     *     @Model(type=VisitFilterForm::class)),
     *   @SWG\Response(response=200, description="Visits found"),
     *   @SWG\Response(response=206, description="Partial content"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Forbidden access")
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
    public function searchVisitsAction(int $id, Request $request)
    {
        return parent::searchVisitsAction($id, $request);
    }


    protected function getVisitedClass() : string
    {
        return User::class;
    }


    protected function getListRoute() : string
    {
        return "rest_get_user_visits";
    }


    protected function getSearchRoute() : string
    {
        return "rest_search_user_visits";
    }

}
