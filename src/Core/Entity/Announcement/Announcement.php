<?php

namespace App\Core\Entity\Announcement;

use App\Core\Entity\Invitation\Invitable;
use App\Core\Entity\User\User;
use App\Core\Entity\Visit\Visitable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Announcement
 *
 * @ORM\Table(name="announcement",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UK_ANNOUNCEMENT_CREATOR", columns={"creator_id"}),
 *     @ORM\UniqueConstraint(name="UK_ANNOUNCEMENT_HOUSING", columns={"housing_id"})
 * }, indexes={
 *     @ORM\Index(name="IDX_ANNOUNCEMENT_TYPE", columns={ "type" }),
 *     @ORM\Index(
 *       name="IDX_ANNOUNCEMENT_LOCATION",
 *       columns={ "location_route", "location_locality", "location_country", "location_zip_code" }),
 *     @ORM\Index(name="IDX_ANNOUNCEMENT_STATUS", columns={ "status" }),
 *     @ORM\Index(name="IDX_ANNOUNCEMENT_RENT_PRICE", columns={ "rent_price" }),
 *     @ORM\Index(name="IDX_ANNOUNCEMENT_START_DATE", columns={ "start_date" }),
 *     @ORM\Index(name="IDX_ANNOUNCEMENT_END_DATE", columns={ "end_date" })
 * })
 * @ORM\Entity(repositoryClass="App\Core\Repository\Announcement\AnnouncementRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="announcements")
 */
class Announcement extends AbstractAnnouncement implements Visitable, Invitable
{
    const STATUS_ENABLED = "enabled";

    const STATUS_DISABLED = "disabled";

    const STATUS_FILLED = "filled";

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="App\Core\Entity\User\User", fetch="LAZY", inversedBy="announcement")
     * @ORM\JoinColumn(name="creator_id", nullable=false)
     */
    protected $creator;

    /**
     * @var Collection<Comment>
     *
     * @ORM\ManyToMany(targetEntity="Comment", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="announcement_comment",
     *   joinColumns={ @ORM\JoinColumn(name="announcement_id", nullable=false) },
     *   inverseJoinColumns={ @ORM\JoinColumn(name="comment_id", unique=true, nullable=false) })
     * @ORM\OrderBy({ "createdAt" = "DESC" })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="announcement_comments")
     */
    protected $comments;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, options={ "default": Announcement::STATUS_ENABLED })
     */
    private $status = self::STATUS_ENABLED;

    /**
     * @var Collection<AnnouncementPicture>
     *
     * @ORM\OneToMany(targetEntity="AnnouncementPicture", mappedBy="announcement", cascade={"persist", "remove"},
     *   fetch="EXTRA_LAZY", orphanRemoval=true)
     */
    private $pictures;

    /**
     * @var Collection<User>
     *
     * @ORM\ManyToMany(targetEntity="App\Core\Entity\User\User", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="announcement_candidate",
     *   joinColumns={
     *     @ORM\JoinColumn(name="announcement_id", nullable=false)
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="user_id", unique=true, nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="announcement_candidates")
     */
    private $candidates;

    /**
     * @var Housing
     *
     * @ORM\OneToOne(targetEntity="Housing", cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\JoinColumn(name="housing_id", nullable=false)
     */
    private $housing;


    /**
     * Constructor
     *
     * @param User $creator The creator of the Announcement
     */
    public function __construct(User $creator)
    {
        parent::__construct($creator);

        $this->pictures = new ArrayCollection();
        $this->candidates = new ArrayCollection();
        $this->housing = new Housing();
    }


    /**
     * @return string
     */
    public function __toString() : string
    {
        return parent::__toString() . "[description = " . $this->description . ", status = " . $this->status . "]";
    }


    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * Set description
     *
     * @param string $description
     *
     * @return Announcement
     */
    public function setDescription(string $description = null)
    {
        $this->description = $description;

        return $this;
    }


    public function getStatus()
    {
        return $this->status;
    }


    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }


    public function isEnabled()
    {
        return $this->status == self::STATUS_ENABLED;
    }


    /**
     * Get pictures
     * @return Collection
     */
    public function getPictures() : Collection
    {
        return $this->pictures;
    }


    public function setPictures(Collection $pictures = null)
    {
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
    public function addPicture(AnnouncementPicture $picture)
    {
        $this->pictures->add($picture);
        $picture->setAnnouncement($this);

        return $this;
    }


    /**
     * Remove picture
     *
     * @param AnnouncementPicture $picture
     */
    public function removePicture(AnnouncementPicture $picture = null)
    {
        if (empty($picture))
        {
            return;
        }

        $this->pictures->removeElement($picture);
    }


    /**
     * Has picture
     *
     * @return boolean
     */
    public function hasPictures()
    {
        return !$this->pictures->isEmpty();
    }


    /**
     * Add candidate
     *
     * @param User $candidate
     *
     * @return Announcement
     */
    public function addCandidate(User $candidate = null)
    {
        if (!$this->candidates->contains($candidate))
        {
            $this->candidates->add($candidate);
        }

        return $this;
    }


    /**
     * Remove candidate
     *
     * @param User $candidate
     */
    public function removeCandidate(User $candidate = null)
    {
        if (empty($candidate))
        {
            return;
        }

        $candidateToDelete = $this->candidates->filter(function (User $c) use ($candidate) {
            return $c->getId() == $candidate->getId();
        })->first();

        $this->candidates->removeElement($candidateToDelete);
    }


    /**
     * Get candidates
     *
     * @return Collection
     */
    public function getCandidates() : Collection
    {
        return $this->candidates;
    }


    /**
     * Set candidates
     *
     * @param Collection $candidates
     *
     * @return \App\Core\Entity\Announcement\Announcement
     */
    public function setCandidates(Collection $candidates = null)
    {
        $this->candidates = $candidates;

        return $this;
    }


    /**
     * Has candidates
     *
     * @return boolean
     */
    public function hasCandidates()
    {
        return !$this->candidates->isEmpty();
    }


    /**
     * Get housing
     *
     * @return Housing
     */
    public function getHousing()
    {
        return $this->housing;
    }


    /**
     * Set housing
     *
     * @param Housing $housing
     *
     * @return Announcement
     */
    public function setHousing(Housing $housing = null)
    {
        $this->housing = $housing;

        return $this;
    }


    public function getInvitees() : Collection
    {
        return $this->getCandidates();
    }


    public function setInvitees(Collection $invitees = null)
    {
        return $this->setCandidates($invitees);
    }


    public function addInvitee(User $invitee = null)
    {
        return $this->addCandidate($invitee);
    }


    public function removeInvitee(User $invitee = null)
    {
        $this->removeCandidate($invitee);
    }


    public function hasInvitee(User $invitee) : bool
    {
        return $this->candidates->contains($invitee);
    }


    public function isAvailable() : bool
    {
        return $this->isEnabled();
    }

}