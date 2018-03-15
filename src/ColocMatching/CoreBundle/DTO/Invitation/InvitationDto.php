<?php

namespace ColocMatching\CoreBundle\DTO\Invitation;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Invitation", allOf={ @SWG\Schema(ref="#/definitions/AbstractDto") })
 * @Hateoas\Relation(
 *   name= "recipient",
 *   href= @Hateoas\Route(name="rest_get_user", absolute=true,
 *     parameters={ "id" = "expr(object.getRecipientId())" })
 * )
 *
 * @author Dahiorus
 */
abstract class InvitationDto extends AbstractDto
{
    /**
     * Invitation status
     * @var string
     * @Assert\NotBlank
     * @Assert\Choice(
     *   choices={ Invitation::STATUS_WAITING, Invitation::STATUS_ACCEPTED, Invitation::STATUS_REFUSED },
     *   strict=true, groups={ "Update" })
     * @Serializer\Expose
     * @SWG\Property(enum={ "waiting", "accepted", "refused" }, default="waiting")
     */
    protected $status;

    /**
     * Invitation message
     * @var string
     * @Serializer\Expose
     * @SWG\Property
     */
    protected $message;

    /**
     * Source type
     * @var string
     * @Serializer\Expose
     * @Serializer\SerializedName("sourceType")
     * @Assert\Choice(
     *   choices={ Invitation::SOURCE_INVITABLE, Invitation::SOURCE_SEARCH }, strict=true)
     * @SWG\Property(enum={ "search", "invitable" }, readOnly=true)
     */
    protected $sourceType;

    /**
     * @var integer
     */
    protected $recipientId;

    /**
     * @var integer
     */
    protected $invitableId;


    /**
     * Creates an InvitationDto depending on the Invitable class
     *
     * @param string $invitableClass The Invitable class
     *
     * @return InvitationDto
     */
    public static function create(string $invitableClass) : InvitationDto
    {
        switch ($invitableClass)
        {
            case Announcement::class:
                return new AnnouncementInvitationDto();
            case Group::class:
                return new GroupInvitationDto();
            default:
                throw new \InvalidArgumentException("'" . $invitableClass . "' not supported");
        }
    }


    public function __toString() : string
    {
        return parent::__toString() . " [status = '" . $this->status . "', message = '" . $this->message
            . "', sourceType = '" . $this->sourceType . ", recipientId = " . $this->recipientId
            . ", invitableId = " . $this->invitableId . "]";
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


    /**
     * Gets the invitable class
     * @return string
     */
    public abstract function getInvitableClass() : string;

}