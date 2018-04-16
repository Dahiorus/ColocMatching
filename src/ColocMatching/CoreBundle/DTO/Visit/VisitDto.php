<?php

namespace ColocMatching\CoreBundle\DTO\Visit;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\VisitableDto;
use ColocMatching\CoreBundle\Entity\Visit\Visit;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Visit", allOf={ @SWG\Schema(ref="#/definitions/AbstractDto") })
 * @Hateoas\Relation(
 *   name="visitor",
 *   href = @Hateoas\Route(name="rest_get_user", absolute=true,
 *     parameters={ "id" = "expr(object.getVisitorId())" })
 * )
 * @Hateoas\Relation(
 *   name="visited",
 *   href = @Hateoas\Route(
 *     name="rest_get_user", absolute=true, parameters={ "id" = "expr(object.getVisitedId())" }),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf="expr(object.getVisitedClass() != 'ColocMatching\\CoreBundle\\Entity\\User\\User')")
 * )
 * @Hateoas\Relation(
 *   name="visited",
 *   href = @Hateoas\Route(
 *     name="rest_get_announcement", absolute=true, parameters={ "id" = "expr(object.getVisitedId())" }),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf="expr(object.getVisitedClass() != 'ColocMatching\\CoreBundle\\Entity\\Announcement\\Announcement')")
 * )
 * @Hateoas\Relation(
 *   name="visited",
 *   href = @Hateoas\Route(
 *     name="rest_get_group", absolute=true, parameters={ "id" = "expr(object.getVisitedId())" }),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf="expr(object.getVisitedClass() != 'ColocMatching\\CoreBundle\\Entity\\Group\\Group')")
 * )
 *
 * @author Dahiorus
 */
class VisitDto extends AbstractDto
{
    /**
     * @var integer
     */
    private $visitedId;

    /**
     * @var string
     */
    private $visitedClass;

    /**
     * @var integer
     */
    private $visitorId;


    /**
     * Instantiates a new VisitDto from the parameters
     *
     * @param UserDto $visitor The visitor
     * @param VisitableDto $visited The visited entity
     *
     * @return VisitDto
     */
    public static function create(UserDto $visitor, VisitableDto $visited) : VisitDto
    {
        $visit = new self();

        $visit->setVisitedClass($visited->getEntityClass());
        $visit->setVisitedId($visited->getId());
        $visit->setVisitorId($visitor->getId());

        return $visit;
    }


    public function getEntityClass() : string
    {
        return Visit::class;
    }


    public function __toString() : string
    {
        return parent::__toString() . "[visitedClass = " . $this->visitedClass . ", visitedId = " . $this->visitedId
            . ", visitorId = " . $this->visitorId . "]";
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


    public function getVisitedClass()
    {
        return $this->visitedClass;
    }


    public function setVisitedClass(string $visitedClass)
    {
        $this->visitedClass = $visitedClass;

        return $this;
    }

}
