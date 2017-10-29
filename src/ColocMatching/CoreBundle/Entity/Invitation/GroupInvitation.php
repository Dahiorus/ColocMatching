<?php

namespace ColocMatching\CoreBundle\Entity\Invitation;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Class GroupInvitation
 *
 * @ORM\Entity()
 * @ORM\Table(name="group_invitation", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="UK_INVITATION_RECIPIENT", columns={ "recipient_id", "group_id" })
 * })
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="GroupInvitation", allOf={ @SWG\Schema(ref="#/definitions/Invitation") })
 * @Hateoas\Relation(
 *   name="self",
 *   href= @Hateoas\Route(name="rest_get_group_invitation", absolute=true,
 *     parameters={ "id" = "expr(object.getInvitable().getId())", "invitationId" = "expr(object.getId())" })
 * )
 * @Hateoas\Relation(
 *   name= "invitable",
 *   href= @Hateoas\Route(name="rest_get_group", absolute=true,
 *     parameters={ "id" = "expr(object.getInvitable().getId())" })
 * )
 */
class GroupInvitation extends Invitation {

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity=Group::class, cascade={ "persist" }, fetch="LAZY")
     * @ORM\JoinColumn(name="group_id", nullable=false, onDelete="CASCADE")
     */
    private $invitable;


    public function __construct(Group $group, User $recipient, string $sourceType) {
        parent::__construct($recipient, $sourceType);
        $this->invitable = $group;
    }


    public function getInvitable() : Invitable {
        return $this->invitable;
    }


    public function setInvitable(Invitable $invitable) {
        $this->invitable = $invitable;
    }
}