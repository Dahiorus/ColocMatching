<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;
use ColocMatching\CoreBundle\Entity\EntityInterface;

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
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(
 *   definition="Announcement", required={"title", "type", "minPrice", "startDate", "location"}
 * )
 */
class Announcement implements EntityInterface {

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
     * @ORM\Column(name="min_price", type="integer")
     * @Assert\GreaterThanOrEqual(value=300)
     * @Assert\NotBlank()
     * @JMS\SerializedName("minPrice")
     * @JMS\Expose()
     * @SWG\Property(description="Announcement minimum price", minimum=300)
     */
    private $minPrice;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_price", type="integer", nullable=true)
     * @Assert\GreaterThanOrEqual(value=300)
     * @JMS\Expose()
     * @JMS\SerializedName("maxPrice")
     * @SWG\Property(description="Announcement maximum price", minimum=300)
     */
    private $maxPrice;

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
     * @ORM\OneToOne(targetEntity="Address", cascade={"persist", "remove"}, fetch="LAZY")
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
     * @SWG\Property(ref="#/definitions/Housing", description="Announcement housing")
     */
    private $housing;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_update", type="datetime", nullable=true)
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
        $this->lastUpdate = new \DateTime();
    }


    /**
     * @return string
     */
    public function __toString() {
        /** @var string */
        $startDate = empty($this->startDate) ? "" : $this->startDate->format(\DateTime::ISO8601);
        $endDate = empty($this->endDate) ? "" : $this->endDate->format(\DateTime::ISO8601);

        return sprintf(
            "Announcement [id: %d, title: '%s', minPrice: %d, maxPrice: %d, description: '%s', startDate: '%s', endDate: '%s',
    			lastUpdate: '%s', location: %s, creator: %s]", $this->id, $this->title, $this->minPrice, $this->maxPrice,
            $this->description, $startDate, $endDate, $this->lastUpdate->format(\DateTime::ISO8601), $this->location,
            $this->creator);
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
     * Set minPrice
     *
     * @param integer $minPrice
     *
     * @return Announcement
     */
    public function setMinPrice(int $minPrice) {
        $this->minPrice = $minPrice;

        return $this;
    }


    /**
     * Get minPrice
     *
     * @return int
     */
    public function getMinPrice() {
        return $this->minPrice;
    }


    /**
     * Set maxPrice
     *
     * @param integer $maxPrice
     *
     * @return Announcement
     */
    public function setMaxPrice(int $maxPrice = null) {
        $this->maxPrice = $maxPrice;

        return $this;
    }


    /**
     * Get maxPrice
     *
     * @return int
     */
    public function getMaxPrice() {
        return $this->maxPrice;
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
    public function getLastUpdate() {
        return $this->lastUpdate;
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
