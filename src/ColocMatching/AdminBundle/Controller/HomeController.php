<?php

namespace ColocMatching\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller {


    /**
     * @Route(methods={"GET"}, path="", name="admin_dashboard")
     */
    public function dashboardAction() {
        $this->get("logger")->info("Getting administration index page");

        // get all users number
        // get all announcements number

        return $this->render("AdminBundle:Home:dashboard.html.twig");
    }

}