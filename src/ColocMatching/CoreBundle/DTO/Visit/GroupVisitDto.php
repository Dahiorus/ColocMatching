<?php

namespace ColocMatching\CoreBundle\DTO\Visit;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Visit\GroupVisit;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *   name= "visited",
 *   href= @Hateoas\Route(name="rest_get_group", absolute=true,
 *     parameters={ "id" = "expr(object.getVisitedId())" })
 * )
 *
 * @author Dahiorus
 */
class GroupVisitDto extends VisitDto
{
    public function getEntityClass() : string
    {
        return GroupVisit::class;
    }


    public function getVisitedClass() : string
    {
        return Group::class;
    }

}