<?php

namespace ColocMatching\AdminBundle\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 *
 * @author Dahiorus
 */
class DisplayController extends Controller {


    /**
     * @Route(methods={"GET"}, path="/{id}", name="admin_user", requirements={"id"="\d+"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPageAction(int $id) {
        $this->get("logger")->info(sprintf("Getting user display page [id: %d]", $id));

        /** @var User */
        $user = $this->get("coloc_matching.core.user_manager")->read($id);

        return $this->render("AdminBundle:User:display.html.twig", array ("user" => $user));
    }

}