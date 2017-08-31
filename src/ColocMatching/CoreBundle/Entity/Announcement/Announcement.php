<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\Updatable;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Validator\Constraint\DateRange;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Announcement
 *
 * @ORM\Table(name="announcement",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UK_ANNOUNCEMENT_CREATOR", columns={"creator_id"}),
 *     @ORM\UniqueConstraint(name="UK_ANNOUNCEMENT_LOCATION", columns={"location_id"}),
 *     @ORM\UniqueConstraint(name="UK_ANNOUNCEMENT_HOUSING", columns={"housing_id"})
 * })
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\Announcement\AnnouncementRepository")
 * @ORM\EntityListeners({
 *   "ColocMatching\CoreBundle\Listener\AnnouncementListener",
 *   "ColocMatching\CoreBundle\Listener\UpdatableListener"
 * })
 * @DateRange()
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Announcement", required={ "title", "type", "rentPrice", "startDate", "location" })
 * @Hateoas\Relation(
 *   name="housing",
 *   href= @Hateoas\Route(name="rest_get_announcement_housing", absolute=true,
 *     parameters={ "id" = "expr(object.getId())" })
 * )
 * @Hateoas\Relation(
 *   name="pictures",
 *   href= @Hateoas\Route(name="rest_get_announcement_pictures", absolute=true,
 *     parameters={ "id" = "expr(object.getId())" })
 * )
 * @Hateoas\Relation(
 *   name="candidates",
 *   href= @Hateoas\Route(name="rest_get_announcement_candidates", absolute=true,
 *     parameters={ "id" = "expr(object.getId())" }),
 *   exclusion= @Hateoas\Exclusion(excludeIf="expr(not is_granted(['ROLE_USER']))")
 * )
 * @Hateoas\Relation(
 *   name="invitations",
 *   href= @Hateoas\Route(
 *     name="rest_get_announcement_invitations", absolute=true, parameters={ "id" = "expr(object.getId())" }),
 *   exclusion= @Hateoas\Exclusion(excludeIf="expr(not is_granted(['ROLE_USER']))")
 * )
 * @Hateoas\Relation(
 *   name="visits",
 *   href= @Hateoas\Route(
 *     name="rest_get_announcement_visits", absolute=true, parameters={ "id" = "expr(object.getId())" }),
 *   exclusion= @Hateoas\Exclusion(excludeIf="expr(not is_granted(['ROLE_USER']))")
 * )
 */
class Announcement extends AbstractAnnouncement implements Updatable, Visitable, Invitable {

    const STATUS_ENABLED = "enabled";

    const STATUS_DISABLED = "disabled";

    const STATUS_FILLED = "filled";

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="LAZY", inversedBy="announcement")
     * @ORM\JoinColumn(name="creator_id", nullable=false)
     * @Assert\NotNull()
     */
    protected $creator;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @JMS\Expose()
     * @SWG\Property(description="Announcement description")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, options={ "default": Announcement::STATUS_ENABLED })
     * @Assert\NotBlank()
     * @Assert\Choice(
     *   choices={ Announcement::STATUS_ENABLED, Announcement::STATUS_DISABLED, Announcement::STATUS_FILLED },
     *   strict=true)
     * @JMS\Expose()
     * @SWG\Property(description="Announcement status", enum={ "enabled", "disabled", "filled" }, default="enabled")
     */
    protected $status = self::STATUS_ENABLED;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="AnnouncementPicture", mappedBy="announcement", cascade={"persist", "remove"},
     *   fetch="EXTRA_LAZY", orphanRemoval=true)
     */
    private $pictures;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="announcement_candidate",
     *   joinColumns={
     *     @ORM\JoinColumn(name="announcement_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="user_id")
     * })
     */
    private $candidates;

    /**
     * @var Housing
     *
     * @ORM\OneToOne(targetEntity="Housing", cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\JoinColumn(name="housing_id", nullable=false)
     * @Assert\Valid()
     * @Assert\NotNull()
     */
    private $housing;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_update", type="datetime")
     */
    private $lastUpdate;


    /**
     * Constructor
     *
     * @param User $creator The creator of the Announcement
     */
    public function __construct(User $creator) {
        $this->setCreator($creator);
        $this->pictures = new ArrayCollection();
        $this->candidates = new ArrayCollection();
        $this->housing = new Housing();
    }


    /**
     * @return string
     */
    public function __toString() {
        /** @var string */
        $createdAt = empty($this->createdAt) ? "" : $this->createdAt->format(\DateTime::ISO8601);
        $lastUpdate = empty($this->lastUpdate) ? "" : $this->lastUpdate->format(\DateTime::ISO8601);
        $startDate = empty($this->startDate) ? "" : $this->startDate->format(\DateTime::ISO8601);
        $endDate = empty($this->endDate) ? "" : $this->endDate->format(\DateTime::ISO8601);

        return sprintf(
            "Announcement [id: %d, title: '%s', rentPrice: %d, description: '%s', startDate: '%s', endDate: '%s', status: '%s', createdAt: '%s', lastUpdate: '%s', location: %s, creator: %s]",
            $this->id, $this->title, $this->rentPrice, $this->description, $startDate, $endDate, $this->status,
            $createdAt, $lastUpdate, $this->location, $this->creator);
    }


    /**
     * Get description
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }


    /**
     * Set description
     *
     * @param string $description
     *
     * @return Announcement
     */
    public function setDescription(string $description = null) {
        $this->description = $description;

        return $this;
    }


    public function getStatus() {
        return $this->status;
    }


    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }


    public function isEnabled() {
        return $this->status == self::STATUS_ENABLED;
    }


    /**
     * Get lastUpdate
     *
     * @return \DateTime
     */
    public function getLastUpdate() : \DateTime {
        return $this->lastUpdate;
    }


    /**
     * Set lastUpdate
     *
     * @param \DateTime $lastUpdate
     *
     * @return Announcement
     */
    public function setLastUpdate(\DateTime $lastUpdate = null) {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }


    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt() : \DateTime {
        return $this->createdAt;
    }


    /**
     * @param \DateTime $createdAt
     *
     * @return \ColocMatching\CoreBundle\Entity\Announcement\Announcement
     */
    public function setCreatedAt(\DateTime $createdAt = null) {
        $this->createdAt = $createdAt;

        return $this;
    }


    /**
     * Get pictures
     * @return Collection
     */
    public function getPictures() : Collection {
        return $this->pictures;
    }


    public function setPictures(Collection $pictures = null) {
        $this->pictures = $pictures;

        return $this;
    }


    /**
     * Add picture
     *
     * @param AnnouncementPicture $picture
     *
     * @return Announcement
     */
    public function addPicture(AnnouncementPicture $picture) {
        $this->pictures->add($picture);

        return $this;
    }


    /**
     * Remove picture
     *
     * @param AnnouncementPicture $picture
     */
    public function removePicture(AnnouncementPicture $picture = null) {
        $this->pictures->removeElement($picture);
    }


    /**
     * Has picture
     *
     * @return boolean
     */
    public function hasPictures() {
        return !$this->pictures->isEmpty();
    }


    /**
     * Add candidate
     *
     * @param User $candidate
     *
     * @return Announcement
     */
    public function addCandidate(User $candidate = null) {
        if (!$this->candidates->contains($candidate)) {
            $this->candidates->add($candidate);
        }

        return $this;
    }


    /**
     * Remove candidate
     *
     * @param User $candidate
     */
    public function removeCandidate(User $candidate = null) {
        $this->candidates->removeElement($candidate);
    }


    /**
     * Get candidates
     *
     * @return Collection
     */
    public function getCandidates() : Collection {
        return $this->candidates;
    }


    /**
     * Set candidates
     *
     * @param Collection $candidates
     *
     * @return \ColocMatching\CoreBundle\Entity\Announcement\Announcement
     */
    public function setCandidates(Collection $candidates = null) {
        $this->candidates = $candidates;

        return $this;
    }


    /**
     * Has candidates
     *
     * @return boolean
     */
    public function hasCandidates() {
        return !$this->candidates->isEmpty();
    }


    /**
     * Get housing
     *
     * @return Housing
     */
    public function getHousing() {
        return $this->housing;
    }


    /**
     * Set housing
     *
     * @param Housing $housing
     *
     * @return Announcement
     */
    public function setHousing(Housing $housing = null) {
        $this->housing = $housing;

        return $this;
    }


    public function getInvitees() : Collection {
        return $this->getCandidates();
    }


    public function setInvitees(Collection $invitees = null) {
        return $this->setCandidates($invitees);
    }


    public function addInvitee(User $invitee = null) {
        return $this->addCandidate($invitee);
    }


    public function removeInvitee(User $invitee = null) {
        $this->removeCandidate($invitee);
    }


    public function isAvailable() : bool {
        return $this->isEnabled();
    }

}
