<?php

namespace ColocMatching\CoreBundle\Controller\Rest;

use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *   definition="RestDataResponse",
 *   description="REST response container for object collections"
 * )
 *
 * @author Dahiorus
 */
class RestDataResponse extends RestResponse {


    public function __construct($data, string $link, string $status = "success") {
        parent::__construct($data, $link, $status);
    }

}