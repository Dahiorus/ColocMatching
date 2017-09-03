<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\User;

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Tag(name="Me", description="Self service")
 */
interface SelfControllerInterface {

    /**
     * Gets the connected user
     *
     * @SWG\Get(path="/me", operationId="rest_get_me", tags={"Me"},
     *   @SWG\Response(response=200, description="User found",
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *   @SWG\Response(response=401, description="Unauthorized access")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getSelfAction(Request $request);


    /**
     * Updates the connected user
     *
     * @SWG\Put(path="/me", operationId="rest_update_me", tags={"Me"},
     *   @SWG\Parameter(
     *     in="body", name="user", required=true,
     *     description="The data to put",
     *
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *
     *   @SWG\Response(response=200, description="User updated",
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateSelfAction(Request $request);


    /**
     * Updates (partial) the connected user
     *
     * @SWG\Patch(path="/me", operationId="rest_patch_me", tags={"Me"},
     *   @SWG\Parameter(
     *     in="body", name="user", required=true,
     *     description="The data to patch",
     *
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *
     *   @SWG\Response(response=200, description="User updated",
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access")
     * )
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function patchSelfAction(Request $request);

}