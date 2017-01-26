<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Announcement
 *
 * @ORM\Table(name="announcement",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="app_announcement_user_unique", columns={"user_id"}),
 *     @ORM\UniqueConstraint(name="app_announcement_location_unique", columns={"location_id"})
 * })
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\Announcement\AnnouncementRepository")
 * @JMS\ExclusionPolicy("ALL")
 */
class Announcement
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank()
     * @JMS\Expose()
     */
    private $title;
    
    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User", inversedBy="announcement", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Assert\NotNull()
     */
    private $owner;

    /**
     * @var int
     *
     * @ORM\Column(name="min_price", type="integer")
     * @Assert\GreaterThanOrEqual(value=300)
     * @Assert\NotBlank()
     * @JMS\Expose()
     */
    private $minPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="max_price", type="integer", nullable=true)
     * @JMS\Expose()
     */
    private $maxPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @JMS\Expose()
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="date")
     * @Assert\Date()
     * @Assert\NotNull()
     * @JMS\Expose()
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="date", nullable=true)
     * @Assert\Date()
     * @JMS\Expose()
     */
    private $endDate;
    
    
    /**
     * @var Address
     *
     * @ORM\OneToOne(targetEntity="Address", cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=false)
     * @Assert\Valid()
     * @Assert\NotNull()
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
     * @var \DateTime
     *
     * @ORM\Column(name="last_update", type="datetime", nullable=true)
     */
    private $lastUpdate;
    
    
    /**
     * Constructor
     * @param User $owner The owner of the Announcement
     */
    public function __construct(User $owner) {
    	$this->owner = $owner;
    	$this->pictures = new ArrayCollection();
    	$this->candidates = new ArrayCollection();
    	$this->lastUpdate = new \DateTime();
    }
    
    
    /**
     * @return string
     */
    public function __toString() {
    	/** @var string */
    	$format = "d/M/Y";
    	$endDate = ($this->endDate) ? $this->endDate->format($format) : "";
    	 
    	return sprintf(
   			"Announcement [id: %d, title: '%s', minPrice: %d, maxPrice: %d, description: '%s', startDate: '%s', endDate: '%s',
    			lastUpdate: '%s', location: %s, owner: %s]",
    		$this->id, $this->title, $this->minPrice, $this->maxPrice, $this->description, $this->startDate->format($format), $endDate,
    			$this->lastUpdate->format(\DateTime::ISO8601), $this->location, $this->owner);
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Announcement
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get owner
     *
     * @return \ColocMatching\CoreBundle\Entity\User\User
     */
    public function getOwner() {
    	return $this->owner;
    }
    
    /**
     * Set the owner
     *
     * @param User $owner
     * @return \ColocMatching\CoreBundle\Entity\Announcement\Announcement
     */
    public function setOwner(User $owner) {
    	$this->owner = $owner;
    	return $this;
    }
    
    /**
     * Set minPrice
     *
     * @param integer $minPrice
     *
     * @return Announcement
     */
    public function setMinPrice(int $minPrice)
    {
        $this->minPrice = $minPrice;

        return $this;
    }

    /**
     * Get minPrice
     *
     * @return int
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * Set maxPrice
     *
     * @param integer $maxPrice
     *
     * @return Announcement
     */
    public function setMaxPrice(int $maxPrice = null)
    {
        $this->maxPrice = $maxPrice;

        return $this;
    }

    /**
     * Get maxPrice
     *
     * @return int
     */
    public function getMaxPrice()
    {
        return $this->maxPrice;
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
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Announcement
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Announcement
     */
    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set lastUpdate
     *
     * @param \DateTime $lastUpdate
     *
     * @return Announcement
     */
    public function setLastUpdate(\DateTime $lastUpdate = null)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate
     *
     * @return \DateTime
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * Set location
     *
     * @param \ColocMatching\CoreBundle\Entity\Announcement\Address $location
     *
     * @return Announcement
     */
    public function setLocation(Address $location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return \ColocMatching\CoreBundle\Entity\Announcement\Address
     */
    public function getLocation()
    {
        return $this->location;
    }
    
    
    /**
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("formatted_location")
     *
     * @return string
     */
    public function getFormattedAddress() {
    	return $this->location->getFormattedAddress();
    }
    
    
    /**
     * Get a short reprensation of the location
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("short_location")
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
    public function removePicture(AnnouncementPicture $picture) {
    	$this->pictures->removeElement($picture);
    }

    
    /**
     * Add candidate
     *
     * @param User $candidate
     * @return Announcement
     */
    public function addCandidate(User $candidate) {
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
    public function removeCandidate(User $candidate) {
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
}
