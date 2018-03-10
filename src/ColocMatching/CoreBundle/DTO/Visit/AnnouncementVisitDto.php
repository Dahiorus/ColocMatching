<?php

namespace ColocMatching\CoreBundle\DTO\Visit;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Visit\AnnouncementVisit;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *   name= "visited",
 *   href= @Hateoas\Route(name="rest_get_announcement", absolute=true,
 *     parameters={ "id" = "expr(object.getVisitedId())" })
 * )
 *
 * @author Dahiorus
 */
class AnnouncementVisitDto extends VisitDto
{
    public function getEntityClass() : string
    {
        return AnnouncementVisit::class;
    }


    public function getVisitedClass() : string
    {
        return Announcement::class;
    }

}