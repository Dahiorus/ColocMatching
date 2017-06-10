<?php

namespace ColocMatching\CoreBundle\Controller\Response;

use Doctrine\Common\Collections\Collection;
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
        $content = $this->content;

        if (is_array($this->content) || $this->content instanceof Collection) {
            $content = json_encode($this->content);
        }

        return "EntityResponse [link=" . $this->link . ", content=" . $content . "]";
    }

}