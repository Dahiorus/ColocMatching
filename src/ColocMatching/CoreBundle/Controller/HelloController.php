<?php

namespace ColocMatching\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HelloController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET"})
     */
    public function indexAction()
    {
        return $this->render('CoreBundle:Hello:hello.html.twig', array(
            "hello" => "hello world"
        ));
    }

}
