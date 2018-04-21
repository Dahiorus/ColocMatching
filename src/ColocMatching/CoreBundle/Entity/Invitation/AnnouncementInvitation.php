<?php

namespace ColocMatching\CoreBundle\Entity\Invitation;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AnnouncementInvitation
 *
 * @ORM\Entity()
 * @ORM\Table(name="announcement_invitation",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UK_ANNOUNCE_INV_RECIPIENT", columns={ "recipient_id", "announcement_id" }),
 * })
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
