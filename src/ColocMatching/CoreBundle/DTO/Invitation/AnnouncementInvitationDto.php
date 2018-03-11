<?php

namespace ColocMatching\CoreBundle\DTO\Invitation;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Invitation\AnnouncementInvitation;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="GroupInvitation", allOf={ @SWG\Schema(ref="#/definitions/Invitation") })
 * @Hateoas\Relation(
 *   name="self",
 *   href= @Hateoas\Route(name="rest_get_announcement_invitation", absolute=true,
 *     parameters={ "id" = "expr(object.getInvitableId())", "invitationId" = "expr(object.getId())" })
 * )
 * @Hateoas\Relation(
 *   name= "invitable",
 *   href= @Hateoas\Route(name="rest_get_announcement", absolute=true,
 *     parameters={ "id" = "expr(object.getInvitableId())" })
 * )
 * @author Dahiorus
 */
class AnnouncementInvitationDto extends InvitationDto
{
    public function getEntityClass() : string
    {
        return AnnouncementInvitation::class;
    }


    public function getInvitableClass() : string
    {
        return Announcement::class;
    }

}
