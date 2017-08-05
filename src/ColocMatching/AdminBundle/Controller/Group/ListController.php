<?php

namespace ColocMatching\AdminBundle\Controller\Group;

use ColocMatching\CoreBundle\Controller\Response\PageResponse;
use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Filter\GroupFilterType;
use ColocMatching\CoreBundle\Manager\Group\GroupManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/group")
 *
 * @author Dahiorus
 */
class ListController extends Controller {

    /**
     * @Route(methods={"GET"}, path="", name="admin_groups")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPageAction() {
        $this->get("logger")->info("Getting group list page");

        return $this->render("AdminBundle:Group:list.html.twig");
    }


    /**
     * @Route(methods={"GET"}, path="/list", name="admin_groups_template_list")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request) {
        $page = $request->query->get("page", RequestConstants::DEFAULT_PAGE);
        $size = $request->query->get("size", RequestConstants::DEFAULT_LIMIT);
        $order = $request->query->get("order", RequestConstants::DEFAULT_ORDER);
        $sort = $request->query->get("sort", RequestConstants::DEFAULT_SORT);

        $this->get("logger")->info("Getting group list template",
            array ("page" => $page, "size" => $size, "order" => $order, "sort" => $sort));

        /** @var GroupManagerInterface */
        $manager = $this->get("coloc_matching.core.group_manager");

        /** @var PageableFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($page, $size, $order, $sort);
        /** @var array */
        $groups = $manager->list($filter);
        /** @var PageResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($groups,
            $manager->countAll(), $filter);

        return $this->render("@includes/page/Group/list/group_table.html.twig",
            array ("response" => $response, "routeName" => $request->get("_route")));
    }


    /**
     * @Route(methods={"POST"}, path="/search", name="admin_groups_template_search")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request) {
        $filterParams = $request->request->all();

        $this->get("logger")->info("Searching groups by filtering", array ("request" => $request));

        /** @var GroupManagerInterface */
        $manager = $this->get("coloc_matching.core.group_manager");

        try {
            /** @var GroupFilter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(
                GroupFilterType::class, new GroupFilter(), $filterParams);

            /** @var array */
            $groups = $manager->search($filter);
            /** @var PageResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($groups,
                $manager->countBy($filter), $filter);

            return $this->render("@includes/page/Group/list/group_table.html.twig",
                array ("response" => $response, "routeName" => $request->get("_route")));
        } catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to search announcements",
                array ("request" => $request, "exception" => $e));

            return new Response($e->getFormError(), $e->getStatusCode());
        }
    }
}