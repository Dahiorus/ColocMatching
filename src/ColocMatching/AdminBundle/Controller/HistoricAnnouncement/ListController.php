<?php

namespace ColocMatching\AdminBundle\Controller\HistoricAnnouncement;

use ColocMatching\CoreBundle\Controller\Response\PageResponse;
use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Filter\HistoricAnnouncementFilterType;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\HistoricAnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/announcement/history")
 *
 * @author Dahiorus
 */
class ListController extends Controller {

    /**
     * @Route(methods={"GET"}, path="", name="admin_announcements_history")
     *
     * @return Response
     */
    public function getPageAction() {
        $this->get("logger")->info("Getting historic announcement list page");

        /** @var HistoricAnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.historic_announcement_manager");

        return $this->render("AdminBundle:HistoricAnnouncement:list.html.twig");
    }


    /**
     * @Route(methods={"GET"}, path="/list", name="admin_announcements_history_template_list")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request) {
        $page = $request->query->get("page", RequestConstants::DEFAULT_PAGE);
        $size = $request->query->get("size", RequestConstants::DEFAULT_LIMIT);
        $order = $request->query->get("order", RequestConstants::DEFAULT_ORDER);
        $sort = $request->query->get("sort", RequestConstants::DEFAULT_SORT);

        $this->get("logger")->info("Getting historic announcement list template",
            array ("page" => $page, "size" => $size, "order" => $order, "sort" => $sort));

        /** @var HistoricAnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.historic_announcement_manager");

        /** @var PageableFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($page, $size, $order, $sort);
        /** @var array */
        $announcements = $manager->list($filter);
        /** @var PageResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($announcements,
            $manager->countAll(), $filter);

        return $this->render("@includes/page/HistoricAnnouncement/list/announcement_table.html.twig",
            array ("response" => $response, "routeName" => $request->get("_route")));
    }


    /**
     * @Route(methods={"POST"}, path="/search", name="admin_announcements_history_template_search")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request) {
        $filterParams = $request->request->all();

        $this->get("logger")->info("Searching historic announcements by filtering", array ("request" => $request));

        /** @var HistoricAnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.historic_announcement_manager");

        try {
            /** @var HistoricAnnouncementFilter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(
                HistoricAnnouncementFilterType::class, new HistoricAnnouncementFilter(), $filterParams);

            /** @var array */
            $announcements = $manager->search($filter);
            /** @var PageResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($announcements,
                $manager->countBy($filter), $filter);

            return $this->render("@includes/page/HistoricAnnouncement/list/announcement_table.html.twig",
                array ("response" => $response, "routeName" => $request->get("_route")));
        } catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to search announcements",
                array ("request" => $request, "exception" => $e));

            return new Response($e->getFormError(), $e->getStatusCode());
        }
    }
}