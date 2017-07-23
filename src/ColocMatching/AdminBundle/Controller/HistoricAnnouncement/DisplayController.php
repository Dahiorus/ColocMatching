<?php

namespace ColocMatching\AdminBundle\Controller\HistoricAnnouncement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/announcement/history")
 *
 * @author Dahiorus
 */
class DisplayController extends Controller {

    /**
     * @Route(methods={"GET"}, path="/{id}", name="admin_announcement_history", requirements={"id"="\d+"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPageAction(int $id) {
        $this->get("logger")->info(sprintf("Getting historic announcement display page [id: %d]", $id));

        /** @var Announcement */
        $announcement = $this->get("coloc_matching.core.historic_announcement_manager")->read($id);

        return $this->render("AdminBundle:HistoricAnnouncement:display.html.twig", array ("announcement" => $announcement));
    }

}