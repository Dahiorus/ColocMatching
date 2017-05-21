<?php

namespace ColocMatching\AdminBundle\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Manager\User\UserManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Form\Type\Filter\UserFilterType;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/user")
 *
 * @author Dahiorus
 */
class ListController extends Controller {


    /**
     * @Route(methods={"GET"}, path="", name="admin_users")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPageAction() {
        $this->get("logger")->info("Getting user list page");

        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");

        $countFilter = new UserFilter();

        $countFilter->setType(UserConstants::TYPE_SEARCH);
        $searchCount = $manager->countBy($countFilter);

        $countFilter->setType(UserConstants::TYPE_PROPOSAL);
        $proposalCount = $manager->countBy($countFilter);

        return $this->render("AdminBundle:User:list.html.twig",
            array ("searchCount" => $searchCount, "proposalCount" => $proposalCount));
    }


    /**
     * @Route(methods={"GET"}, path="/list", name="admin_user_template_list")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request) {
        $page = $request->query->get("page", RequestConstants::DEFAULT_PAGE);
        $size = $request->query->get("size", RequestConstants::DEFAULT_LIMIT);
        $order = $request->query->get("order", RequestConstants::DEFAULT_ORDER);
        $sort = $request->query->get("sort", RequestConstants::DEFAULT_SORT);

        $this->get("logger")->info(
            sprintf("Getting user list template [page: %d, size: %d, order: '%s', sort: '%s']", $page, $size, $order,
                $sort));

        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");

        /** @var UserFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->setFilter(new UserFilter(), $page, $size, $order,
            $sort);
        /** @var array */
        $users = $manager->list($filter);
        /** @var RestListResponse */
        $response = $this->get("coloc_matching.core.rest_response_factory")->createRestListResponse($users,
            $manager->countAll(), $filter);

        return $this->render("@includes/page/User/list/user_table.html.twig",
            array ("response" => $response, "routeName" => $request->get("_route")));
    }


    /**
     * @Route(methods={"POST"}, path="/search", name="admin_user_template_search")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request) {
        $filterParams = $request->request->all();

        $this->get("logger")->info("Searching users by filter",
            array ("filterData" => $filterParams, "request" => $request));

        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");

        try {
            /** @var AnnouncementFilter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(UserFilterType::class,
                new UserFilter(), $filterParams);

            /** @var array */
            $users = $manager->search($filter);
            /** @var RestListResponse */
            $response = $this->get("coloc_matching.core.rest_response_factory")->createRestListResponse($users,
                $manager->countBy($filter), $filter);

            return $this->render("@includes/page/User/list/user_table.html.twig",
                array ("response" => $response, "routeName" => $request->get("_route")));
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to search users",
                array ("request" => $request, "exception" => $e));

            return new Response($e->getFormError(), $e->getStatusCode());
        }
    }

}