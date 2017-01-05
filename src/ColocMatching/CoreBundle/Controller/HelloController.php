<?php

namespace ColocMatching\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use ColocMatching\CoreBundle\Form\Type\User\UserType;

class HelloController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET"})
     */
    public function indexAction()
    {
    	$form = $this->createForm(UserType::class);
    	
        return $this->render('CoreBundle:Hello:hello.html.twig', array(
            "hello" => "hello world",
        	"form"  => $form->createView(),
        ));
    }

}
