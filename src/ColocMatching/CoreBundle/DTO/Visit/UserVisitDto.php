<?php

namespace ColocMatching\CoreBundle\DTO\Visit;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\UserVisit;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *   name= "visited",
 *   href= @Hateoas\Route(name="rest_get_user", absolute=true,
 *     parameters={ "id" = "expr(object.getVisitedId())" })
 * )
 *
 * @author Dahiorus
 */
class UserVisitDto extends VisitDto
{
    public function getEntityClass() : string
    {
        return UserVisit::class;
    }


    public function getVisitedClass() : string
    {
        return User::class;
    }
}