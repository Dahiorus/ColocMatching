<?php

namespace ColocMatching\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("")
 *
 * @author Dahiorus
 */
class SecurityController extends Controller {


    /**
     * @Route(name="admin_login", methods={"GET", "POST"}, path="/login")
     */
    public function loginAction(Request $request) {
        $this->get("logger")->info("Getting admin login page");

        /** @var AuthenticationUtils */
        $authenticationUtils = $this->get("security.authentication_utils");

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render("AdminBundle:Security:login.html.twig",
            array ("lastUsername" => $lastUsername, "error" => $error));
    }


    /**
     * @Route(name="admin_logout", methods={"GET"}, path="/logout")
     */
    public function logoutAction() {
        $this->get("logger")->info("Logging out of the administration");
    }

}