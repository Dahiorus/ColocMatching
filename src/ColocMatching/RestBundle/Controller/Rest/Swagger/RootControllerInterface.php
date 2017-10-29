<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger;

use Swagger\Annotations as SWG;

/**
 * Class RestController
 *
 * @SWG\Swagger(
 *   host="coloc-matching.api",
 *   basePath="/rest",
 *   schemes={"http"},
 *   swagger="2.0",
 *   consumes={ "application/json" },
 *   produces={ "application/json" },
 *   @SWG\Info(version="1.0.0", title="ColocMatching REST API"),
 *   @SWG\SecurityScheme(
 *     securityDefinition="api_token", type="apiKey", in="header", name="Authorization"),
 * )
 *
 * @author brondon.ung
 */
interface RootControllerInterface {

}