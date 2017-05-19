<?php

namespace ColocMatching\AdminBundle\Controller;

use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;

/**
 * @Route("")
 *
 * @author Dahiorus
 */
class HomeController extends Controller {


    /**
     * @Route(methods={"GET"}, path="", name="admin_dashboard")
     */
    public function dashboardAction() {
        $this->get("logger")->info("Getting administration index page");

        $createdAt = new \DateTime("4 days ago");

        // get total count
        $announcementCount = $this->get("coloc_matching.core.announcement_manager")->countAll();
        $userCount = $this->get("coloc_matching.core.user_manager")->countAll();

        // get latest announcements
        $announcementFilter = new AnnouncementFilter();
        $announcementFilter->setCreatedAtSince($createdAt);
        $announcements = $this->get("coloc_matching.core.announcement_manager")->search($announcementFilter);

        // get latest users
        $userFilter = new UserFilter();
        $userFilter->setCreatedAtSince($createdAt);
        $users = $this->get("coloc_matching.core.user_manager")->search($userFilter);

        $this->get("logger")->info("Rendering administration index page",
            [ "users" => $users, "announcements" => $announcements]);

        return $this->render("AdminBundle:Home:dashboard.html.twig",
            array (
                "announcementCount" => $announcementCount,
                "userCount" => $userCount,
                "latestAnnouncements" => $announcements,
                "latestUsers" => $users));
    }

}