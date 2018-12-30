<?php

namespace App\Rest\Controller\v1\Visit;

use App\Core\Entity\Announcement\Announcement;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Form\Type\Filter\VisitFilterForm;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Visit\VisitDtoManagerInterface;
use App\Core\Repository\Filter\Converter\StringConverterInterface;
use App\Core\Validator\FormValidator;
use App\Rest\Controller\Response\Visit\VisitCollectionResponse;
use App\Rest\Controller\Response\Visit\VisitPageResponse;
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
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for the resource /announcements/{id}/visits
 *
 * @Rest\Route(path="/announcements/{id}/visits")
 * @Security(expression="is_granted('ROLE_USER')")
 *
 * @author Dahiorus
 */
class AnnouncementVisitController extends AbstractVisitedVisitController
{
    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, VisitDtoManagerInterface $visitManager,
        AnnouncementDtoManagerInterface $visitedManager, FormValidator $formValidator, RouterInterface $router,
        StringConverterInterface $stringConverter)
    {
        parent::__construct($logger, $serializer, $authorizationChecker, $visitManager, $visitedManager,
            $formValidator, $router, $stringConverter);
    }


    /**
     * Lists visits done on an announcement
     *
     * @Rest\Get(name="rest_get_announcement_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters (prefix with '-' to DESC sort)",
     *   default="-createdAt")
     *
     * @Operation(tags={ "Visits" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Response(response=200, description="Visits found", @Model(type=VisitPageResponse::class)),
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
     * Searches specific visits done on an announcement
     *
     * @Rest\Post(path="/searches", name="rest_search_announcement_visits")
     *
     * @Operation(tags={ "Visits" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(name="filter", in="body", required=true, description="Criteria filter",
     *     @Model(type=VisitFilterForm::class)),
     *   @SWG\Response(response=201, description="Visits found", @Model(type=VisitCollectionResponse::class)),
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


    /**
     * Gets searched visits on an announcement from the base 64 JSON string filter
     *
     * @Rest\Get(path="/searches/{filter}", name="rest_get_searched_announcement_visits")
     *
     * @Operation(tags={ "Visits" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(
     *     name="filter", in="path", type="string", required=true, description="Base 64 JSON string filter"),
     *   @SWG\Response(response=200, description="Visits found", @Model(type=VisitCollectionResponse::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="Unsupported base64 string conversion")
     * )
     *
     * @param int $id
     * @param string $filter
     *
     * @return JsonResponse
     * @throws ORMException
     */
    public function getSearchedVisitsAction(int $id, string $filter)
    {
        return parent::getSearchedVisitsAction($id, $filter);
    }


    protected function getVisitedClass() : string
    {
        return Announcement::class;
    }


    protected function getListRoute() : string
    {
        return "rest_get_announcement_visits";
    }


    protected function getSearchRoute() : string
    {
        return "rest_get_searched_announcement_visits";
    }

}
