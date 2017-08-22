<?php

namespace ColocMatching\CoreBundle\Entity\Invitation;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Class AnnouncementInvitation
 *
 * @ORM\Entity()
 * @ORM\Table(name="announcement_invitation",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UK_INVITATION_RECIPIENT", columns={ "recipient_id", "announcement_id" }),
 * })
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="AnnouncementInvitation")
 */
class AnnouncementInvitation extends Invitation {

    /**
     * @var Announcement
     *
     * @ORM\ManyToOne(targetEntity=Announcement::class, cascade={ "persist" }, fetch="LAZY")
     * @ORM\JoinColumn(name="announcement_id", nullable=false, onDelete="CASCADE")
     * @JMS\Expose()
     * @SWG\Property(description="The announcement related to the invitation", ref="#/definitions/Announcement")
     */
    private $invitable;


    public function __construct(Announcement $announcement, User $recipient, string $sourceType) {
        parent::__construct($recipient, $sourceType);
        $this->invitable = $announcement;
    }


    public function getInvitable() : Invitable {
        return $this->invitable;
    }


    public function setInvitable(Invitable $invitable) {
        $this->invitable = $invitable;
    }

}