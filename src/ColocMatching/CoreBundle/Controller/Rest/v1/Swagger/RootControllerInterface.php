<?php

namespace ColocMatching\CoreBundle\Controller\Rest\v1\Swagger;

use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class RestController
 *
 * @SWG\Swagger(
 *   host="coloc-matching.api",
 *   basePath="/rest",
 *   schemes={"http"},
 *   swagger="2.0",
 *   consumes={
 *     "application/json",
 *     "multipart/form-data"
 *   },
 *   produces={ "application/json" },
 *
 *   @SWG\Info(
 *     version="1.0.0",
 *     title="ColocMatching REST API"
 * ))
 *
 * @author brondon.ung
 */
interface RootControllerInterface {

}