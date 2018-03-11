<?php

namespace ColocMatching\CoreBundle\Entity\Invitation;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Class AnnouncementInvitation
 *
 * @ORM\Entity()
 * @ORM\Table(name="announcement_invitation",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UK_ANNOUNCE_INV_RECIPIENT", columns={ "recipient_id", "announcement_id" }),
 * })
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="AnnouncementInvitation", allOf={ @SWG\Schema(ref="#/definitions/Invitation") })
 * @Hateoas\Relation(
 *   name="self",
 *   href= @Hateoas\Route(name="rest_get_announcement_invitation", absolute=true,
 *     parameters={ "id" = "expr(object.getInvitable().getId())", "invitationId" = "expr(object.getId())" })
 * )
 * @Hateoas\Relation(
 *   name= "invitable",
 *   href= @Hateoas\Route(name="rest_get_announcement", absolute=true,
 *     parameters={ "id" = "expr(object.getInvitable().getId())" })
 * )
 */
class AnnouncementInvitation extends Invitation
{
    /**
     * @var Announcement
     *
     * @ORM\ManyToOne(targetEntity=Announcement::class, cascade={ "persist" }, fetch="LAZY")
     * @ORM\JoinColumn(name="announcement_id", nullable=false, onDelete="CASCADE")
     */
    protected $invitable;

}
