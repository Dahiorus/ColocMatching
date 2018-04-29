<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Manager\Group\GroupDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Visit\VisitDtoManagerInterface;
use ColocMatching\CoreBundle\Validator\FormValidator;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for resource /groups/{id}/visits
 *
 * @Rest\Route(path="/groups/{id}/visits", service="coloc_matching.rest.group_visit_controller")
 * @Security(expression="has_role('ROLE_USER')")
 *
 * @author Dahiorus
 */
class GroupVisitController extends AbstractVisitedVisitController
{
    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, VisitDtoManagerInterface $visitManager,
        GroupDtoManagerInterface $visitedManager, FormValidator $formValidator)
    {
        parent::__construct($logger, $serializer, $authorizationChecker, $visitManager, $visitedManager,
            $formValidator);
    }


    /**
     * @Rest\Get(name="rest_get_group_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\QueryParam(name="sorts", map=true, nullable=true, requirements="\w+,(asc|desc)", default="createdAt,desc",
     *   allowBlank=false, description="Sorting parameters")
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
     * @Rest\Post(path="/searches", name="rest_search_group_visits")
     * @Rest\RequestParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\RequestParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     * @Rest\RequestParam(name="sorts", map=true, nullable=true, requirements="\w+,(asc|desc)",
     *     default="createdAt,desc", allowBlank=false, description="Sorting parameters")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function searchVisitsAction(int $id, ParamFetcher $paramFetcher, Request $request)
    {
        return parent::searchVisitsAction($id, $paramFetcher, $request);
    }


    protected function getVisitedClass() : string
    {
        return Group::class;
    }


    protected function getListRoute() : string
    {
        return "rest_get_group_visits";
    }


    protected function getSearchRoute() : string
    {
        return "rest_search_group_visits";
    }

}