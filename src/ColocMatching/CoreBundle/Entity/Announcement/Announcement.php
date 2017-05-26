<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Validator\Constraint\DateRange;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;
use ColocMatching\CoreBundle\Entity\Updatable;

/**
 * Announcement
 *
 * @ORM\Table(name="announcement",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="app_announcement_user_unique", columns={"user_id"}),
 *     @ORM\UniqueConstraint(name="app_announcement_location_unique", columns={"location_id"}),
 *     @ORM\UniqueConstraint(name="app_announcement_housing_unique", columns={"housing_id"})
 * })
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\Announcement\AnnouncementRepository")
 * @ORM\EntityListeners({"ColocMatching\CoreBundle\Listener\UpdatableListener", "ColocMatching\CoreBundle\Listener\AnnouncementListener"})
 * @DateRange()
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(
 *   definition="Announcement", required={"title", "type", "rentPrice", "startDate", "location"}
 * )
 */
class Announcement implements EntityInterface, Updatable {

    const TYPE_RENT = "rent";

    const TYPE_SUBLEASE = "sublease";

    const TYPE_SHARING = "sharing";

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     * @SWG\Property(description="Annnouncement ID", readOnly=true)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank()
     * @JMS\Expose()
     * @SWG\Property(description="Annnouncement title")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Choice(choices={Announcement::TYPE_RENT, Announcement::TYPE_SUBLEASE, Announcement::TYPE_SHARING}, strict=true)
     * @JMS\Expose()
     * @SWG\Property(description="Annnouncement type", enum={ "rent", "sublease", "sharing" })
     */
    private $type;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User", inversedBy="announcement", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull()
     */
    private $creator;

    /**
     * @var integer
     *
     * @ORM\Column(name="rent_price", type="integer")
     * @Assert\GreaterThanOrEqual(value=0)
     * @Assert\NotBlank()
     * @JMS\SerializedName("rentPrice")
     * @JMS\Expose()
     * @SWG\Property(description="Announcement rent price", minimum=0)
     */
    private $rentPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @JMS\Expose()
     * @SWG\Property(description="Announcement description")
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="date")
     * @Assert\Date()
     * @Assert\NotNull()
     * @JMS\Expose()
     * @JMS\SerializedName("startDate")
     * @SWG\Property(description="Announcement start date", format="date")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="date", nullable=true)
     * @Assert\Date()
     * @JMS\Expose()
     * @JMS\SerializedName("endDate")
     * @SWG\Property(description="Announcement end date", format="date")
     */
    private $endDate;

    /**
     * @var Address
     *
     * @ORM\OneToOne(targetEntity="Address", cascade={"persist", "merge", "remove"}, fetch="LAZY")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=false)
     * @Assert\Valid()
     * @Assert\NotNull()
     * @SWG\Property(type="string", description="Announcement location")
     */
    private $location;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AnnouncementPicture", mappedBy="announcement", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $pictures;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="announcement_candidate",
     *   joinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="announcement_id", referencedColumnName="id")
     * })
     */
    private $candidates;

    /**
     * @var Housing
     *
     * @ORM\OneToOne(targetEntity="Housing", cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\JoinColumn(name="housing_id", referencedColumnName="id", nullable=false)
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
     * @param User $creator The creator of the Announcement
     */
    public function __construct(User $creator) {
        $this->creator = $creator;
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
            "Announcement [id: %d, title: '%s', rentPrice: %d, description: '%s', startDate: '%s', endDate: '%s', createdAt: '%s',
                lastUpdate: '%s', location: %s, creator: %s]", $this->id,
            $this->title, $this->rentPrice, $this->description, $startDate, $endDate, $createdAt, $lastUpdate,
            $this->location, $this->creator);
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }


    /**
     * Set title
     *
     * @param string $title
     *
     * @return Announcement
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }


    /**
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }


    /**
     * Set type
     *
     * @param string $type
     * @return Announcement
     */
    public function setType($type) {
        $this->type = $type;

        return $this;
    }


    /**
     * Get type
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }


    /**
     * Get creator
     *
     * @return \ColocMatching\CoreBundle\Entity\User\User
     */
    public function getCreator() {
        return $this->creator;
    }


    /**
     * Set creator
     *
     * @param User $creator
     * @return \ColocMatching\CoreBundle\Entity\Announcement\Announcement
     */
    public function setCreator(User $creator) {
        $this->creator = $creator;
        return $this;
    }


    /**
     * Set rentPrice
     *
     * @param integer $rentPrice
     *
     * @return Announcement
     */
    public function setRentPrice(int $rentPrice) {
        $this->rentPrice = $rentPrice;

        return $this;
    }


    /**
     * Get minPrice
     *
     * @return int
     */
    public function getRentPrice() {
        return $this->rentPrice;
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


    /**
     * Get description
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }


    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Announcement
     */
    public function setStartDate(\DateTime $startDate = null) {
        $this->startDate = $startDate;

        return $this;
    }


    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate() {
        return $this->startDate;
    }


    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Announcement
     */
    public function setEndDate(\DateTime $endDate = null) {
        $this->endDate = $endDate;

        return $this;
    }


    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate() {
        return $this->endDate;
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
     * Get lastUpdate
     *
     * @return \DateTime
     */
    public function getLastUpdate(): \DateTime {
        return $this->lastUpdate;
    }


    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime {
        return $this->createdAt;
    }


    /**
     * @param \DateTime $createdAt
     * @return \ColocMatching\CoreBundle\Entity\Announcement\Announcement
     */
    public function setCreatedAt(\DateTime $createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }


    /**
     * Set location
     *
     * @param \ColocMatching\CoreBundle\Entity\Announcement\Address $location
     *
     * @return Announcement
     */
    public function setLocation(Address $location = null) {
        $this->location = $location;

        return $this;
    }


    /**
     * Get location
     *
     * @return \ColocMatching\CoreBundle\Entity\Announcement\Address
     */
    public function getLocation() {
        return $this->location;
    }


    /**
     * Formatted representation of the location
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("formattedLocation")
     * @SWG\Property(property="formattedLocation", type="string", readOnly=true)
     *
     * @return string
     */
    public function getFormattedAddress() {
        return $this->location->getFormattedAddress();
    }


    /**
     * Short reprensation of the location
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("shortLocation")
     * @SWG\Property(property="shortLocation", type="string", readOnly=true)
     *
     * @return string
     */
    public function getShortLocation() {
        return $this->location->getShortAddress();
    }


    /**
     * Get pictures
     * @return ArrayCollection
     */
    public function getPictures() {
        return $this->pictures;
    }


    public function setPictures(ArrayCollection $pictures = null) {
        $this->pictures = $pictures;
        return $this;
    }


    /**
     * Add picture
     *
     * @param AnnouncementPicture $picture
     * @return Announcement
     */
    public function addPicture(AnnouncementPicture $picture) {
        $this->pictures[] = $picture;
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
     * @return Announcement
     */
    public function addCandidate(User $candidate = null) {
        if (!$this->candidates->contains($candidate)) {
            $this->candidates[] = $candidate;
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
     * @return ArrayCollection
     */
    public function getCandidates() {
        return $this->candidates;
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
     * @return Announcement
     */
    public function setHousing(Housing $housing = null) {
        $this->housing = $housing;
        return $this;
    }

}
