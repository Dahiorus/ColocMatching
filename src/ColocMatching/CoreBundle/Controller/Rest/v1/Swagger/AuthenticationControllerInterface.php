<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1\Swagger;

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SWG\Tag(name="Authentication", description="Operations on user authentication")
 *
 * @author Dahiorus
 *
 */
interface AuthenticationControllerInterface {


    /**
     * Authenticates a user and creates an authentication token
     *
     * @SWG\Post(path="/auth-tokens/", operationId="rest_create_authtoken",
     *   tags={ "Authentication" },
     *
     *   @SWG\Parameter(
     *     in="body", name="credentials", required=true,
     *     description="The User credentials",
     *
     *     @SWG\Schema(
     *       type="object", required={"_username", "_password"},
     *
     *       @SWG\Property(
     *         property="_username", type="string",
     *         description="The User's username"
     *       ),
     *       @SWG\Property(
     *         property="_password", type="string",
     *         description="The User's password"
     *       )
     *   )),
     *
     *   @SWG\Response(
     *     response=201, description="Authentication token created",
     *
     *     @SWG\Schema(title="AuthToken", type="object",
     *
     *       @SWG\Property(
     *         property="token", type="string",
     *         description="The authentication token"
     *       ),
     *       @SWG\Property(
     *         property="user", title="User", type="object",
     *         description="The User's information",
     *
     *         @SWG\Property(property="id", type="integer", description="User's Id"),
     *         @SWG\Property(property="username", type="string", description="User's username"),
     *         @SWG\Property(property="name", type="string", description="User's display name"),
     *         @SWG\Property(property="type", type="string", description="User's type")
     *       )
     *   )),
     *   @SWG\Response(response=403, description="Authentication error"),
     * )
     *
     * @param Request $request
     */
    public function postAuthTokenAction(Request $request);

}
