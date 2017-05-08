<?php

namespace ColocMatching\AdminBundle\Controller;

use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Controller\Rest\RestListResponse;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface;

class AnnouncementController extends Controller {


    /**
     * @Route(methods={"GET"}, path="", name="admin_announcements")
     */
    public function getPageAction() {
        $this->get("logger")->info("Getting announcement list page");

        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        $countFilter = new AnnouncementFilter();

        $countFilter->setTypes(array (Announcement::TYPE_RENT));
        $rentCount = $manager->countBy($countFilter);

        $countFilter->setTypes(array (Announcement::TYPE_SUBLEASE));
        $subleaseCount = $manager->countBy($countFilter);

        $countFilter->setTypes(array (Announcement::TYPE_SHARING));
        $sharingCount = $manager->countBy($countFilter);

        return $this->render("AdminBundle:Announcement:list.html.twig",
            array ("rentCount" => $rentCount, "subleaseCount" => $subleaseCount, "sharingCount" => $sharingCount));
    }


    /**
     * @Route(methods={"GET"}, path="/list", name="admin_announcement_list")
     */
    public function listAction(Request $request) {
        $page = $request->query->get("page", RequestConstants::DEFAULT_PAGE);
        $size = $request->query->get("limit", RequestConstants::DEFAULT_LIMIT);
        $order = $request->query->get("order", RequestConstants::DEFAULT_ORDER);
        $sort = $request->query->get("sort", RequestConstants::DEFAULT_SORT);

        $this->get("logger")->info(
            sprintf("Getting announcement list page [page: %d, size: %d, order: '%s', sort: '%s']", $page, $size,
                $order, $sort));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        /** @var AnnouncementFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->setFilter(new AnnouncementFilter(), $page, $size,
            $order, $sort);
        /** @var array */
        $announcements = $manager->list($filter);
        /** @var RestListResponse */
        $response = $this->get("coloc_matching.core.rest_response_factory")->createRestListResponse($announcements,
            $this->get("coloc_matching.core.announcement_manager")->countAll(), $filter);

        return $this->render("@includes/page/Announcement/announcement_list.html.twig",
            array ("announcements" => $response->getData(), "response" => $response));
    }

}