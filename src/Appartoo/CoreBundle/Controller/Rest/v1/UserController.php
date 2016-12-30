<?php

namespace Appartoo\CoreBundle\Controller\Rest\v1;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Configuration : voir routing.yml -> appartoo.core.api.user
 */
class UserController extends Controller
{   
    /**
     * @Rest\Get()
     */
    public function getUsersAction() {
        /** @var array */
        $users = $this->get('appartoo.core.user_manager')->getAll();
        
        return new \Symfony\Component\HttpFoundation\JsonResponse(array('data' => $users), 418);
    }
}
