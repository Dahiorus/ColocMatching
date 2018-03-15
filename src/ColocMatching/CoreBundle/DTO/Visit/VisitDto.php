<?php

namespace ColocMatching\CoreBundle\DTO\Visit;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Visit", allOf={ @SWG\Schema(ref="#/definitions/AbstractDto") })
 * @Hateoas\Relation(
 *   name= "visitor",
 *   href= @Hateoas\Route(name="rest_get_user", absolute=true,
 *     parameters={ "id" = "expr(object.getVisitorId())" })
 * )
 *
 * @author Dahiorus
 */
abstract class VisitDto extends AbstractDto
{
    /**
     * @var integer
     */
    private $visitedId;

    /**
     * @var integer
     */
    private $visitorId;


    public function __toString() : string
    {
        return parent::__toString() . "[visitedId = " . $this->visitedId . ", visitorId = " . $this->visitorId . "]";
    }


    public function getVisitedId() : ?int
    {
        return $this->visitedId;
    }


    public function setVisitedId(?int $visitedId)
    {
        $this->visitedId = $visitedId;

        return $this;
    }


    public function getVisitorId()
    {
        return $this->visitorId;
    }


    public function setVisitorId(?int $visitorId)
    {
        $this->visitorId = $visitorId;

        return $this;
    }


    /**
     * Gets the visited class
     * @return string
     */
    public abstract function getVisitedClass() : string;
}
