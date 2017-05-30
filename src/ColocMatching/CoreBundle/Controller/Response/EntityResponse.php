<?php

namespace ColocMatching\CoreBundle\Controller\Response;

use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *   definition="EntityResponse",
 *   description="Response container for entity",
 *   discriminator="content"
 * )
 *
 * @author Dahiorus
 */
class EntityResponse extends AbstractResponse {


    public function __construct($data, string $link) {
        parent::__construct($data, $link);
    }


    public function __toString() {
        $content = empty($this->content) ? "" : (is_array($this->content) ? json_encode($this->content) : $this->content);

        return sprintf("EntityResponse [link: '%s', content: %s]", $this->link, $content);
    }

}