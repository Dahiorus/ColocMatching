<?php

namespace ColocMatching\AdminBundle\Controller\Announcement;

use ColocMatching\CoreBundle\Controller\Response\PageResponse;
use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Filter\AnnouncementFilterType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/announcement")
 *
 * @author Dahiorus
 */
class ListController extends Controller {


    /**
     * @Route(methods={"GET"}, path="", name="admin_announcements")
     *
     * @return \Symfony\Component\HttpFoundation\Response
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
     * @Route(methods={"GET"}, path="/list", name="admin_announcements_template_list")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request) {
        $page = $request->query->get("page", RequestConstants::DEFAULT_PAGE);
        $size = $request->query->get("size", RequestConstants::DEFAULT_LIMIT);
        $order = $request->query->get("order", RequestConstants::DEFAULT_ORDER);
        $sort = $request->query->get("sort", RequestConstants::DEFAULT_SORT);

        $this->get("logger")->info("Getting announcement list template",
            array ("page" => $page, "size" => $size, "order" => $order, "sort" => $sort));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        /** @var AnnouncementFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($page, $size, $order, $sort);
        /** @var array */
        $announcements = $manager->list($filter);
        /** @var PageResponse */
        $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($announcements,
            $manager->countAll(), $filter);

        return $this->render("@includes/page/Announcement/list/announcement_table.html.twig",
            array ("response" => $response, "routeName" => $request->get("_route")));
    }


    /**
     * @Route(methods={"POST"}, path="/search", name="admin_announcements_template_search")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request) {
        $filterParams = $request->request->all();

        $this->get("logger")->info("Searching announcements by filtering", array ("request" => $request));

        /** @var AnnouncementManagerInterface */
        $manager = $this->get("coloc_matching.core.announcement_manager");

        try {
            /** @var AnnouncementFilter */
            $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(
                AnnouncementFilterType::class, new AnnouncementFilter(), $filterParams);

            /** @var array */
            $announcements = $manager->search($filter);
            /** @var PageResponse */
            $response = $this->get("coloc_matching.core.response_factory")->createPageResponse($announcements,
                $manager->countBy($filter), $filter);

            return $this->render("@includes/page/Announcement/list/announcement_table.html.twig",
                array ("response" => $response, "routeName" => $request->get("_route")));
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to search announcements",
                array ("request" => $request, "exception" => $e));

            return new Response($e->getFormError(), $e->getStatusCode());
        }
    }

}