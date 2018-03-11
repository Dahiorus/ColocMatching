<?php

namespace ColocMatching\CoreBundle\Entity\Invitation;

use ColocMatching\CoreBundle\Entity\AbstractEntity;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Invitation
 *
 * @ORM\MappedSuperclass(repositoryClass="ColocMatching\CoreBundle\Repository\Invitation\InvitationRepository")
 * @ORM\EntityListeners({
 *   "ColocMatching\CoreBundle\Listener\UpdateListener",
 *   "ColocMatching\CoreBundle\Listener\InvitationListener"
 * })
 *
 * @author Dahiorus
 */
abstract class Invitation extends AbstractEntity
{
    const STATUS_WAITING = "waiting";
    const STATUS_ACCEPTED = "accepted";
    const STATUS_REFUSED = "refused";

    const SOURCE_SEARCH = "search";
    const SOURCE_INVITABLE = "invitable";

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="recipient_id", nullable=false, onDelete="CASCADE")
     */
    protected $recipient;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, options={ "default": Invitation::STATUS_WAITING })
     */
    protected $status = self::STATUS_WAITING;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    protected $message;

    /**
     * @var string
     *
     * @ORM\Column(name="source_type", type="string")
     */
    protected $sourceType;

    /**
     * @var Invitable
     */
    protected $invitable;


    protected function __construct(Invitable $invitable, User $recipient, string $sourceType)
    {
        $this->invitable = $invitable;
        $this->recipient = $recipient;
        $this->sourceType = $sourceType;
    }


    public function __toString()
    {
        return parent::__toString() . " [status = '" . $this->status . "', message = '" . $this->message
            . "', sourceType = '" . $this->sourceType . "]";
    }


    /**
     * Creates a new instance of Invitation
     *
     * @param Invitable $invitable The invitable of the invitation
     * @param User $recipient The recipient of the invitation
     * @param string $sourceType The source type of the invitation
     *
     * @return Invitation|null
     */
    public static function create(Invitable $invitable, User $recipient, string $sourceType)
    {
        if ($invitable instanceof Announcement)
        {
            return new AnnouncementInvitation($invitable, $recipient, $sourceType);
        }

        if ($invitable instanceof Group)
        {
            return new GroupInvitation($invitable, $recipient, $sourceType);
        }

        throw new \InvalidArgumentException("'" . get_class($invitable) . "' not supported");
    }


    public function getRecipient() : User
    {
        return $this->recipient;
    }


    public function setRecipient(User $recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }


    public function getStatus()
    {
        return $this->status;
    }


    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }


    public function getMessage()
    {
        return $this->message;
    }


    public function setMessage(string $message)
    {
        $this->message = $message;

        return $this;
    }


    public function getSourceType()
    {
        return $this->sourceType;
    }


    public function setSourceType(string $sourceType)
    {
        $this->sourceType = $sourceType;
    }


    public function getInvitable() : Invitable
    {
        return $this->invitable;
    }


    public function setInvitable(Invitable $invitable)
    {
        $this->invitable = $invitable;
    }
}
