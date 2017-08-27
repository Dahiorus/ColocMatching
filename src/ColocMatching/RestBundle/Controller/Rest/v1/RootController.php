<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1;

use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\RootControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Root controller of the REST API
 *
 * @author brondon.ung
 */
class RootController extends RestController implements RootControllerInterface {

    /**
     * @Rest\Get("", name="rest_root")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function indexAction() {
        $this->get("logger")->info("Getting API information");

        return new JsonResponse(
            array (
                "_links" => array (
                    "self" => "/rest",
                    "resources" => array (
                        "authentication" => array ("link" => "/auth-tokens/", "methods" => ["POST"]),
                        "announcements" => array (
                            "link" => "/announcements",
                            "methods" => array ("GET", "POST", "PUT", "DELETE", "PATCH")),
                        "users" => array (
                            "link" => "/users",
                            "methods" => array ("GET", "POST", "PUT", "DELETE", "PATCH"))))), Response::HTTP_OK);
    }

}
