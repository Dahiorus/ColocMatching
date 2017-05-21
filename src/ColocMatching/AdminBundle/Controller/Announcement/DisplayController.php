<?php

namespace ColocMatching\AdminBundle\Controller\Announcement;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;

/**
 * @Route(path="/announcement")
 *
 * @author Dahiorus
 */
class DisplayController extends Controller {


    /**
     * @Route(methods={"GET"}, path="/{id}", name="admin_announcement", requirements={"id"="\d+"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPageAction(int $id) {
        $this->get("logger")->info(sprintf("Getting announcement display page [id: %d]", $id));

        /** @var Announcement */
        $announcement = $this->get("coloc_matching.core.announcement_manager")->read($id);

        return $this->render("AdminBundle:Announcement:display.html.twig", array ("announcement" => $announcement));
    }

}