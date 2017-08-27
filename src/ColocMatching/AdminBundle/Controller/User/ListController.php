<?php

namespace ColocMatching\AdminBundle\Controller\User;

use ColocMatching\AdminBundle\Controller\RequestConstants;
use ColocMatching\AdminBundle\Controller\Response\Page;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Filter\UserFilterType;
use ColocMatching\CoreBundle\Manager\User\UserManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request) {
        $page = $request->query->get("page", RequestConstants::DEFAULT_PAGE);
        $size = $request->query->get("size", RequestConstants::DEFAULT_LIMIT);
        $order = $request->query->get("order", RequestConstants::DEFAULT_ORDER);
        $sort = $request->query->get("sort", RequestConstants::DEFAULT_SORT);

        $this->get("logger")->info("Getting user list template",
            array ("page" => $page, "size" => $size, "order" => $order, "sort" => $sort));

        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");

        /** @var UserFilter $filter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($page, $size, $order, $sort);
        /** @var array<User> $users */
        $users = $manager->list($filter);
        /** @var Page $response */
        $response = new Page($filter, $users, $manager->countAll());

        return $this->render("@includes/page/User/list/user_table.html.twig",
            array ("response" => $response, "routeName" => $request->get("_route")));
    }


    /**
     * @Route(methods={"POST"}, path="/search", name="admin_user_template_search")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request) {
        $filterParams = $request->request->all();

        $this->get("logger")->info("Searching users by filtering", array ("request" => $request));

        /** @var UserManagerInterface */
        $manager = $this->get("coloc_matching.core.user_manager");

        try {
            /** @var AnnouncementFilter $filter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(UserFilterType::class,
                new UserFilter(), $filterParams);

            /** @var array */
            $users = $manager->search($filter);
            /** @var Page $response */
            $response = new Page($filter, $users, $manager->countBy($filter));

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