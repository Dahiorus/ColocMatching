<?php

namespace ColocMatching\CoreBundle\Entity\User;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\EntityInterface;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AnnouncementPreference
 *
 * @ORM\Table(name="announcement_preference", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UK_ANNOUNCEMENT_PREF_ADDRESS", columns={"address_id"})
 * })
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="AnnouncementPreference")
 */
class AnnouncementPreference implements EntityInterface {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     * @SWG\Property(description="AnnouncementPreference id", readOnly=true)
     */
    private $id;

    /**
     * @var Address
     *
     * @ORM\OneToOne(targetEntity = "ColocMatching\CoreBundle\Entity\Announcement\Address",
     *     cascade={"persist", "merge"}, fetch="EAGER")
     * @ORM\JoinColumn(name="address_id", unique=true)
     * @Assert\Valid()
     */
    private $address;

    /**
     * @var integer
     *
     * @ORM\Column(name="rent_price_start", type="integer", nullable=true)
     * @JMS\SerializedName("rentPriceStart")
     * @JMS\Expose()
     * @SWG\Property(description="Rent price start range filter")
     */
    private $rentPriceStart;

    /**
     * @var integer
     *
     * @ORM\Column(name="rent_price_end", type="integer", nullable=true)
     * @JMS\SerializedName("rentPriceEnd")
     * @JMS\Expose()
     * @SWG\Property(description="Rent price end range filter")
     */
    private $rentPriceEnd;

    /**
     * @var array
     *
     * @ORM\Column(name="types", type="array", nullable=true)
     * @JMS\Expose()
     * @Assert\Choice(choices={ Announcement::TYPE_RENT, Announcement::TYPE_SUBLEASE, Announcement::TYPE_SHARING },
     *   multiple=true, strict=true)
     * @SWG\Property(description="Announcement types filter", enum={ "rent", "sublease", "sharing" },
     *   @SWG\Items(type="string"))
     */
    private $types = [];

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date_after", type="date", nullable=true)
     * @JMS\SerializedName("startDateAfter")
     * @JMS\Expose()
     * @Assert\Date()
     * @SWG\Property(description="Start date 'from' filter", format="date")
     */
    private $startDateAfter;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date_before", type="date", nullable=true)
     * @JMS\SerializedName("startDateBefore")
     * @JMS\Expose()
     * @Assert\Date()
     * @SWG\Property(description="Start date 'to' filter", format="date")
     */
    private $startDateBefore;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date_after", type="date", nullable=true)
     * @JMS\SerializedName("endDateAfter")
     * @JMS\Expose()
     * @Assert\Date()
     * @SWG\Property(description="End date 'from' filter", format="date")
     */
    private $endDateAfter;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date_before", type="date", nullable=true)
     * @JMS\SerializedName("endDateBefore")
     * @JMS\Expose()
     * @Assert\Date()
     * @SWG\Property(description="End date 'to' filter", format="date")
     */
    private $endDateBefore;

    /**
     * @var boolean
     *
     * @ORM\Column(name="with_pictures", type="boolean", options={"default": false})
     * @JMS\SerializedName("withPictures")
     * @JMS\Expose()
     * @SWG\Property(description="Only announcements with pictures")
     */
    private $withPictures = false;


    public function __toString() : string {
        $startDateAfter = empty($this->startDateAfter) ? "" : $this->startDateAfter->format(\DateTime::ISO8601);
        $startDateBefore = empty($this->startDateBefore) ? "" : $this->startDateBefore->format(\DateTime::ISO8601);
        $endDateAfter = empty($this->endDateAfter) ? "" : $this->endDateAfter->format(\DateTime::ISO8601);
        $endDateBefore = empty($this->endDateBefore) ? "" : $this->endDateBefore->format(\DateTime::ISO8601);

        return sprintf(
            "AnnouncementPreference [id: %d, address: %s, rentPrice: [%d - %d], types: [%s], startDate: ['%s' - '%s'], endDate: ['%s' - '%s'], withPictures: %d]",
            $this->id, $this->address, $this->rentPriceStart, $this->rentPriceEnd, implode(", ", $this->types),
            $startDateAfter, $startDateBefore, $endDateAfter, $endDateBefore, $this->withPictures);
    }


    public function getId() : int {
        return $this->id;
    }


    public function setId(int $id) {
        $this->id = $id;

        return $this;
    }


    public function getAddress() {
        return $this->address;
    }


    public function setAddress(Address $address = null) {
        $this->address = $address;

        return $this;
    }


    public function getRentPriceStart() {
        return $this->rentPriceStart;
    }


    public function setRentPriceStart(int $rentPriceStart = null) {
        $this->rentPriceStart = $rentPriceStart;

        return $this;
    }


    public function getRentPriceEnd() {
        return $this->rentPriceEnd;
    }


    public function setRentPriceEnd(int $rentPriceEnd = null) {
        $this->rentPriceEnd = $rentPriceEnd;

        return $this;
    }


    public function getTypes() {
        return $this->types;
    }


    public function setTypes(array $types = null) {
        $this->types = $types;

        return $this;
    }


    public function getStartDateAfter() {
        return $this->startDateAfter;
    }


    public function setStartDateAfter(\DateTime $startDateAfter = null) {
        $this->startDateAfter = $startDateAfter;

        return $this;
    }


    public function getStartDateBefore() {
        return $this->startDateBefore;
    }


    public function setStartDateBefore(\DateTime $startDateBefore = null) {
        $this->startDateBefore = $startDateBefore;

        return $this;
    }


    public function getEndDateAfter() {
        return $this->endDateAfter;
    }


    public function setEndDateAfter(\DateTime $endDateAfter = null) {
        $this->endDateAfter = $endDateAfter;

        return $this;
    }


    public function getEndDateBefore() {
        return $this->endDateBefore;
    }


    public function setEndDateBefore(\DateTime $endDateBefore = null) {
        $this->endDateBefore = $endDateBefore;

        return $this;
    }


    public function withPictures() {
        return $this->withPictures;
    }


    public function setWithPictures(bool $withPictures) {
        $this->withPictures = $withPictures;

        return $this;
    }


    /**
     * Formatted representation of the address
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("address")
     * @SWG\Property(property="address", type="string")
     *
     * @return string
     */
    public function getFormattedAddress() {
        if (empty($this->address)) {
            return null;
        }

        return $this->address->getFormattedAddress();
    }

}