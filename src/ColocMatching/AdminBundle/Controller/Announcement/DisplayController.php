<?php

namespace ColocMatching\AdminBundle\Controller\Announcement;

use ColocMatching\AdminBundle\Controller\RequestConstants;
use ColocMatching\AdminBundle\Controller\Response\Page;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/announcement")
 *
 * @author Dahiorus
 */
class DisplayController extends Controller {

    /**
     * @Route(methods={"GET"}, path="/{id}", name="admin_announcement", requirements={"id"="\d+"})
     *
     * @param int $id
     *
     * @return Response
     */
    public function getPageAction(int $id) {
        $this->get("logger")->info(sprintf("Getting announcement display page [id: %d]", $id));

        /** @var Announcement */
        $announcement = $this->get("coloc_matching.core.announcement_manager")->read($id);

        return $this->render("AdminBundle:Announcement:display.html.twig", array ("announcement" => $announcement));
    }


    /**
     * @Route(methods={"GET"}, path="/{id}/visits/list", name="admin_announcement_visits", requirements={"id"="\d+"})
     *
     * @param $id
     * @param $request
     *
     * @return Response
     */
    public function listVisits(int $id, Request $request) {
        $page = $request->query->get("page", RequestConstants::DEFAULT_PAGE);

        $this->get("logger")->info("Listing the visits of an announcement", array ("id" => $id, "page" => $page));

        $visitManager = $this->get("coloc_matching.core.announcement_visit_manager");
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($page, 5,
            PageableFilter::ORDER_DESC, "visitedAt");
        $announcement = $this->get("coloc_matching.core.announcement_manager")->read($id);
        $visits = $visitManager->listByVisited($announcement, $filter);
        $response = new Page($filter, $visits, $visitManager->countByVisited($announcement));

        $this->get("logger")->info("Listing visits - result information", array ("response" => $response));

        return $this->render("@includes/page/Announcement/display/visit_table.html.twig",
            array ("response" => $response, "id" => $id));
    }

}