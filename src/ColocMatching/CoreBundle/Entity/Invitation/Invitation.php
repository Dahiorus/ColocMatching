<?php

namespace ColocMatching\CoreBundle\Entity\Invitation;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Updatable;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Invitation
 *
 * @ORM\MappedSuperclass(repositoryClass="ColocMatching\CoreBundle\Repository\Invitation\InvitationRepository")
 * @ORM\EntityListeners({
 *   "ColocMatching\CoreBundle\Listener\UpdatableListener",
 *   "ColocMatching\CoreBundle\Listener\InvitationListener"
 * })
 * @JMS\ExclusionPolicy("ALL")
 *
 * @author Dahiorus
 */
abstract class Invitation implements Updatable {

    const STATUS_WAITING = "waiting";
    const STATUS_ACCEPTED = "accepted";
    const STATUS_REFUSED = "refused";

    const SOURCE_SEARCH = "search";
    const SOURCE_INVITABLE = "invitable";

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     * @SWG\Property(description="Invitation identifier", readOnly=true)
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="recipient_id", nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     * @JMS\Expose()
     * @SWG\Property(description="The recipient", ref="#/definitions/User", readOnly=true)
     */
    protected $recipient;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, options={ "default": Invitation::STATUS_WAITING })
     * @Assert\NotBlank()
     * @Assert\Choice(
     *   choices={ Invitation::STATUS_WAITING, Invitation::STATUS_ACCEPTED, Invitation::STATUS_REFUSED },
     *   strict=true, groups={ "Update" })
     * @JMS\Expose()
     * @SWG\Property(description="Invitation status", enum={ "waiting", "accepted", "refused" }, default="waiting")
     */
    protected $status = self::STATUS_WAITING;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     * @JMS\Expose()
     * @SWG\Property(description="Invitation message")
     */
    protected $message;

    /**
     * @var string
     *
     * @ORM\Column(name="source_type", type="string")
     * @JMS\Expose()
     * @JMS\SerializedName("sourceType")
     * @Assert\Choice(choices={ Invitation::SOURCE_INVITABLE, Invitation::SOURCE_SEARCH },
     *   strict=true)
     * @SWG\Property(description="Source type", enum={ "search", "invitable" }, readOnly=true)
     */
    protected $sourceType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @JMS\Expose()
     * @JMS\SerializedName("createdAt")
     * @SWG\Property(description="Creation date", type="date", format="datetime", readOnly=true)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_update", type="datetime")
     */
    protected $lastUpdate;


    protected function __construct(User $recipient, string $sourceType) {
        $this->recipient = $recipient;
        $this->sourceType = $sourceType;
    }


    public function __toString() {
        $createdAt = empty($this->createdAt) ? "" : $this->createdAt->format(\DateTime::ISO8601);
        $lastUpdate = empty($this->lastUpdate) ? "" : $this->lastUpdate->format(\DateTime::ISO8601);

        return "Invitation(" . $this->id . ")" . " [status='" . $this->status . "', message='" . $this->message .
            "', sourceType='" . $this->sourceType . ", createdAt=" . $createdAt . ", lastUpdate=" . $lastUpdate . "]";
    }


    /**
     * Creates a new instance of Invitation
     *
     * @param Invitable $invitable The invitable of the invitation
     * @param User $recipient      The recipient of the invitation
     * @param string $sourceType   The source type of the invitation
     *
     * @return Invitation|null
     */
    public static function create(Invitable $invitable, User $recipient, string $sourceType) {
        if ($invitable instanceof Announcement) {
            return new AnnouncementInvitation($invitable, $recipient, $sourceType);
        }

        if ($invitable instanceof Group) {
            return new GroupInvitation($invitable, $recipient, $sourceType);
        }

        return null;
    }


    public function getId() : int {
        return $this->id;
    }


    public function setId(int $id) {
        $this->id = $id;
    }


    public function getRecipient() : User {
        return $this->recipient;
    }


    public function setRecipient(User $recipient) {
        $this->recipient = $recipient;

        return $this;
    }


    public function getStatus() {
        return $this->status;
    }


    public function setStatus(string $status) {
        $this->status = $status;

        return $this;
    }


    public function getMessage() {
        return $this->message;
    }


    public function setMessage(string $message) {
        $this->message = $message;

        return $this;
    }


    public function getSourceType() {
        return $this->sourceType;
    }


    public function setSourceType(string $sourceType) {
        $this->sourceType = $sourceType;
    }


    public function getCreatedAt() : \DateTime {
        return $this->createdAt;
    }


    public function setCreatedAt(\DateTime $createdAt) {
        $this->createdAt = $createdAt;
    }


    public function getLastUpdate() : \DateTime {
        return $this->lastUpdate;
    }


    public function setLastUpdate(\DateTime $lastUpdate) {
        $this->lastUpdate = $lastUpdate;
    }


    public abstract function getInvitable() : Invitable;


    public abstract function setInvitable(Invitable $invitable);

}
