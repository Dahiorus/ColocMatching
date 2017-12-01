<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger;

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Tag(name="Registration")
 */
interface RegistrationControllerInterface {

    /**
     * Registers a new user and sends a confirmation e-mail
     *
     * @SWG\Post(path="/registrations", operationId="rest_post_registration", tags={ "Registration" },
     *   @SWG\Parameter(
     *     in="body", name="user", required=true, description="The data to post",
     *     @SWG\Schema(ref="#/definitions/User")),
     *   @SWG\Response(response=201, description="Registration succeeded"),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function registerAction(Request $request);
}