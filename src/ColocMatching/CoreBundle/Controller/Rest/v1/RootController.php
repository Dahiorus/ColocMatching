<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Controller\Rest\v1\Swagger\RootControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Root controller of the REST API
 *
 * @author brondon.ung
 */
class RootController extends Controller implements RootControllerInterface {


    /**
     * @Rest\Get("", name="rest_root")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function indexAction() {
        $this->get('logger')->info('Get API information');

        return new JsonResponse(
            array (
                "_links" => array (
                    "self" => "/rest",
                    "resources" => array (
                        "authentication" => array ("link" => "/auth-tokens/", "methods" => [ "POST"]),
                        "announcements" => array (
                            "link" => "/announcements",
                            "methods" => array ("GET", "POST", "PUT", "DELETE", "PATCH")),
                        "users" => array (
                            "link" => "/users",
                            "methods" => array ("GET", "POST", "PUT", "DELETE", "PATCH"))))), Response::HTTP_OK);
    }

}
