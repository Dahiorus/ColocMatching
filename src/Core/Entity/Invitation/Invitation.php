<?php

namespace App\Core\Entity\Invitation;

use App\Core\Entity\AbstractEntity;
use App\Core\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Invitation
 *
 * @ORM\Entity(repositoryClass="App\Core\Repository\Invitation\InvitationRepository")
 * @ORM\Table(name="invitation", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="UK_INVITATION", columns={ "recipient_id", "invitable_id", "invitable_class" })
 * }, indexes={
 *   @ORM\Index(name="IDX_INVITATION_RECIPENT", columns={ "recipient_id" }),
 *   @ORM\Index(name="IDX_INVITATION_INVITABLE", columns={ "invitable_class", "invitable_id" }),
 *   @ORM\Index(name="IDX_INVITATION_INVITABLE_CLASS", columns={ "invitable_class" }),
 *   @ORM\Index(name="IDX_INVITATION_STATUS", columns={ "status" }),
 *   @ORM\Index(name="IDX_INVITATION_SOURCE_TYPE", columns={ "source_type" })
 * })
 * @ORM\EntityListeners({
 *   "App\Core\Listener\UpdateListener",
 *   "App\Core\Listener\CacheDriverListener"
 * })
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="invitations")
 *
 * @author Dahiorus
 */
class Invitation extends AbstractEntity
{
    const STATUS_WAITING = "waiting";
    const STATUS_ACCEPTED = "accepted";
    const STATUS_REFUSED = "refused";

    const SOURCE_SEARCH = "search"; // from a search user
    const SOURCE_INVITABLE = "invitable"; // from an invitable entity

    /**
     * @var string
     * @ORM\Column(name="invitable_class", nullable=false)
     */
    private $invitableClass;

    /**
     * @var int
     * @ORM\Column(name="invitable_id", type="bigint", nullable=false)
     */
    private $invitableId;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Core\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="recipient_id", nullable=false, onDelete="CASCADE")
     */
    private $recipient;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, options={ "default": Invitation::STATUS_WAITING })
     */
    private $status = self::STATUS_WAITING;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="source_type", type="string")
     */
    private $sourceType;


    public function __construct(string $invitableClass, int $invitableId, User $recipient, string $sourceType)
    {
        $this->invitableClass = $invitableClass;
        $this->invitableId = $invitableId;
        $this->recipient = $recipient;
        $this->sourceType = $sourceType;
    }


    public function __toString()
    {
        return parent::__toString() . " [invitableClass = " . $this->invitableClass
            . ", invitableId = " . $this->invitableId . ", status = " . $this->status . ", message = " . $this->message
            . ", sourceType = " . $this->sourceType . "]";
    }


    public function getInvitableClass()
    {
        return $this->invitableClass;
    }


    public function setInvitableClass(?string $invitableClass)
    {
        $this->invitableClass = $invitableClass;

        return $this;
    }


    public function getInvitableId()
    {
        return $this->invitableId;
    }


    public function setInvitableId(?int $invitableId) : Invitation
    {
        $this->invitableId = $invitableId;

        return $this;
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


    public function setMessage(?string $message)
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

}
