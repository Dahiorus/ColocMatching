<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Description of RootController
 * @author brondon.ung
 */
class RootController extends Controller {

    /**
     * @Rest\Get("", name="rest_root")
     */
    public function indexAction() {
    	$this->get('logger')->info('Get API information');
    	
        return new JsonResponse(array(
            "_links" => array(
                "self" => "/rest",
                "resources" => array(
					"authentication" => array (
						"link" => "/auth-tokens",
						"methods" => ["POST"]
					),
                	"users" => array (
                		"link" => "/users",
                		"methods" => ["GET", "POST", "PUT", "DELETE", "PATCH"]
                	),
                ),
            ),
        ), Response::HTTP_OK);
    }
}
