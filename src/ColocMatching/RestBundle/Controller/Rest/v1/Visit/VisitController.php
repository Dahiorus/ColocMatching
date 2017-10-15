<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\Form\Type\Filter\VisitFilterType;
use ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\Visit\VisitControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Rest\Route("/visits")
 * @Security(expression="has_role('ROLE_API')")
 */
class VisitController extends RestController implements VisitControllerInterface {

    private const USER = "user";
    private const ANNOUNCEMENT = "announcement";
    private const GROUP = "group";


    /**
     * Lists visits
     *
     * @Rest\Get(path="", name="rest_get_visits")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="20")
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results",
     *   default="id")
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$",
     *   default="asc")
     * @Rest\QueryParam(name="type", nullable=false, description="The invitable type",
     *   requirements="^(announcement|group|user)$")
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getVisitsAction(ParamFetcher $paramFetcher) {
        $pageable = $this->extractPageableParameters($paramFetcher);
        $visitableType = $paramFetcher->get("type");

        $this->get("logger")->info("Listing visits",
            array ("visitableType" => $visitableType, "pageable" => $pageable));

        /** @var PageableFilter $filter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($pageable["page"],
            $pageable["size"], $pageable["order"], $pageable["sort"]);
        /** @var array<Visit> $visits */
        $visits = $this->getManager($visitableType)->list($filter);
        /** @var PageResponse $response */
        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($visits,
            $this->getManager($visitableType)->countAll(), $filter);

        $this->get("logger")->info("Listing visits - result information",
            array ("response" => $response));

        return $this->buildJsonResponse($response,
            $response->hasNext() ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Searches visits
     *
     * @Rest\Post(path="/searches", name="rest_search_visits")
     * @Rest\QueryParam(name="type", nullable=false, description="The invitable type",
     *   requirements="^(announcement|group|user)$")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchVisitsAction(Request $request) {
        $visitableType = $request->query->get("type");

        $this->get("logger")->info("Searching visits", array ("type" => $visitableType, "request" => $request));

        /** @var VisitFilter $filter */
        $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(VisitFilterType::class,
            new VisitFilter(), $request->request->all());
        /** @var array<Visit> $visits */
        $visits = $this->getManager($visitableType)->search($filter);
        /** @var PageResponse $response */
        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($visits,
            $this->getManager($visitableType)->countBy($filter), $filter);

        $this->get("logger")->info("Searching visits - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            $response->hasNext() ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    private function getManager(string $visitableType) : VisitManagerInterface {
        $manager = null;

        switch ($visitableType) {
            case self::USER:
                $manager = $this->get("coloc_matching.core.user_visit_manager");
                break;
            case self::ANNOUNCEMENT:
                $manager = $this->get("coloc_matching.core.announcement_visit_manager");
                break;
            case self::GROUP:
                $manager = $this->get("coloc_matching.core.group_visit_manager");
                break;
            default:
                throw new \Exception("Unknown visitable type");
                break;
        }

        return $manager;
    }

}