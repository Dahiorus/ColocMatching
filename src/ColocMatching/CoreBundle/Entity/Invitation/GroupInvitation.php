<?php

namespace ColocMatching\CoreBundle\Entity\Invitation;

use ColocMatching\CoreBundle\Entity\Group\Group;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class GroupInvitation
 *
 * @ORM\Entity
 * @ORM\Table(name="group_invitation", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="UK_GRP_INV_RECIPIENT", columns={ "recipient_id", "group_id" })
 * })
 *
 */
class GroupInvitation extends Invitation
{
    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity=Group::class, cascade={ "persist" }, fetch="LAZY")
     * @ORM\JoinColumn(name="group_id", nullable=false, onDelete="CASCADE")
     */
    protected $invitable;

}
