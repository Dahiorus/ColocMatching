<?php

namespace App\Core\DTO\Invitation;

use App\Core\DTO\AbstractDto;
use App\Core\DTO\Annotation\RelatedEntity;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\User\User;
use App\Core\Validator\Constraint\UniqueValue;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("ALL")
 * @UniqueValue(properties={ "invitableId", "invitableClass", "recipientId" })
 *
 * @Hateoas\Relation(
 *   name= "recipient",
 *   href= @Hateoas\Route(name="rest_get_user", absolute=true,
 *     parameters={ "id" = "expr(object.getRecipientId())" })
 * )
 * @Hateoas\Relation(
 *   name="invitable",
 *   href = @Hateoas\Route(name="rest_get_group", absolute=true,
 *     parameters={ "id" = "expr(object.getInvitableId())" }),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf="expr(object.getInvitableClass() != 'ColocMatching\\CoreBundle\\Entity\\Group\\Group')")
 * )
 * @Hateoas\Relation(
 *   name="invitable",
 *   href = @Hateoas\Route(name="rest_get_announcement", absolute=true,
 *     parameters={ "id" = "expr(object.getInvitableId())" }),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf="expr(object.getInvitableClass() != 'ColocMatching\\CoreBundle\\Entity\\Announcement\\Announcement')")
 * )
 *
 * @author Dahiorus
 */
class InvitationDto extends AbstractDto
{
    /**
     * Invitation status
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(
     *   choices={ Invitation::STATUS_WAITING, Invitation::STATUS_ACCEPTED, Invitation::STATUS_REFUSED }, strict=true)
     * @Serializer\Expose
     * @SWG\Property(property="status", type="string", default="waiting", readOnly=true)
     */
    private $status;

    /**
     * Invitation message
     * @var string
     *
     * @Serializer\Expose
     * @SWG\Property(property="message", type="string")
     */
    private $message;

    /**
     * Source type
     * @var string
     *
     * @Serializer\Expose
     * @Serializer\SerializedName("sourceType")
     * @Assert\Choice(
     *   choices={ Invitation::SOURCE_INVITABLE, Invitation::SOURCE_SEARCH }, strict=true)
     * @SWG\Property(property="sourceType", type="string", readOnly=true)
     */
    private $sourceType;

    /**
     * @var integer
     *
     * @RelatedEntity(targetClass=User::class, targetProperty="recipient")
     * @Assert\NotNull
     */
    private $recipientId;

    /**
     * @var string
     *
     * @Assert\NotNull
     */
    private $invitableClass;

    /**
     * @var integer
     *
     * @Assert\NotNull
     */
    private $invitableId;


    /**
     * Creates a new InvitationDto from the invitable and the recipient
     *
     * @param InvitableDto $invitable The invitable
     * @param UserDto $recipient The recipient
     * @param string $sourceType The source type
     *
     * @return InvitationDto
     */
    public static function create(InvitableDto $invitable, UserDto $recipient, string $sourceType) : InvitationDto
    {
        $invitation = new self();

        $invitation->setInvitableClass($invitable->getEntityClass());
        $invitation->setInvitableId($invitable->getId());
        $invitation->setRecipientId($recipient->getId());
        $invitation->setSourceType($sourceType);
        $invitation->setStatus(Invitation::STATUS_WAITING);

        return $invitation;
    }


    public function __toString() : string
    {
        return parent::__toString() . " [invitableClass = " . $this->invitableClass
            . ", invitableId = " . $this->invitableId . ", status = " . $this->status . ", message = " . $this->message
            . ", sourceType = " . $this->sourceType . ", recipientId = " . $this->recipientId . "]";
    }


    public function getStatus()
    {
        return $this->status;
    }


    public function setStatus(?string $status)
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


    public function setSourceType(?string $sourceType)
    {
        $this->sourceType = $sourceType;

        return $this;
    }


    public function getRecipientId()
    {
        return $this->recipientId;
    }


    public function setRecipientId(?int $recipientId)
    {
        $this->recipientId = $recipientId;

        return $this;
    }


    public function getInvitableId()
    {
        return $this->invitableId;
    }


    public function setInvitableId(?int $invitableId)
    {
        $this->invitableId = $invitableId;

        return $this;
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


    public function getEntityClass() : string
    {
        return Invitation::class;
    }

}