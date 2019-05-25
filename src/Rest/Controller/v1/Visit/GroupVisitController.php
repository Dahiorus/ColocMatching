<?php

namespace App\Rest\Controller\v1\Visit;

use App\Core\Entity\Group\Group;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\Visit\VisitDtoManagerInterface;
use App\Core\Repository\Filter\Converter\StringConverterInterface;
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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for the resource /groups/{id}/visits
 *
 * @Rest\Route(path="/groups/{id}/visits")
 * @Security(expression="is_granted('ROLE_USER')")
 *
 * @author Dahiorus
 */
class GroupVisitController extends AbstractVisitedVisitController
{
    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, VisitDtoManagerInterface $visitManager,
        GroupDtoManagerInterface $visitedManager, StringConverterInterface $stringConverter)
    {
        parent::__construct($logger, $serializer, $authorizationChecker, $visitManager, $visitedManager,
            $stringConverter);
    }


    /**
     * Lists visits done on a group
     *
     * @Rest\Get(name="rest_get_group_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters (prefix with '-' to DESC sort)",
     *   default="-createdAt")
     * @Rest\QueryParam(
     *   name="q", nullable=true,
     *   description="Search query to filter results (csv), parameters are in the form 'name:value'")
     *
     * @Operation(tags={ "Visits" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
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


    protected function getVisitedClass() : string
    {
        return Group::class;
    }


    protected function getListRoute() : string
    {
        return "rest_get_group_visits";
    }

}
