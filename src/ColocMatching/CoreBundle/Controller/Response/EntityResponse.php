<?php

namespace ColocMatching\CoreBundle\Controller\Response;

use ColocMatching\CoreBundle\Controller\Response\AbstractResponse;
use Swagger\Annotations as SWG;
use ColocMatching\CoreBundle\Entity\EntityInterface;

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


    public function __construct(EntityInterface $data, string $link) {
        parent::__construct($data, $link);
    }


    public function __toString() {
        return sprintf("EntityResponse [link: '%s', content: %s]", $this->link, $this->content);
    }

}