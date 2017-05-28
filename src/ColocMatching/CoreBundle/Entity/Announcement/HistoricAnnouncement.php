<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use ColocMatching\CoreBundle\Entity\User\User;

/**
 * Historic announcement created at the deletion of an Announcement
 *
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\Announcement\HistoricAnnouncementRepository")
 * @ORM\Table(name="historic_announcement",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="app_announcement_user_unique", columns={"user_id"}),
 *     @ORM\UniqueConstraint(name="app_announcement_location_unique", columns={"location_id"})
 * })
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="HistoricAnnouncement")
 *
 * @author Dahiorus
 */
class HistoricAnnouncement implements EntityInterface {

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
     * @JMS\Expose()
     * @SWG\Property(description="Annnouncement title")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     * @JMS\Expose()
     * @SWG\Property(description="Annnouncement type", enum={ "rent", "sublease", "sharing" })
     */
    private $type;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $creator;

    /**
     * @var integer
     *
     * @ORM\Column(name="rent_price", type="integer")
     * @JMS\SerializedName("rentPrice")
     * @JMS\Expose()
     * @SWG\Property(description="Announcement rent price", minimum=300)
     */
    private $rentPrice;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="date")
     * @JMS\Expose()
     * @JMS\SerializedName("startDate")
     * @SWG\Property(description="Announcement start date", format="date")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="date", nullable=true)
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
     * @SWG\Property(type="string", description="Announcement location")
     */
    private $location;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;


    public function __construct(Announcement $announcement) {
        $this->type = $announcement->getType();
        $this->title = $announcement->getTitle();
        $this->location = $announcement->getLocation();
        $this->creator = $announcement->getCreator();
        $this->rentPrice = $announcement->getRentPrice();
        $this->startDate = $announcement->getStartDate();
        $this->endDate = $announcement->getEndDate();
        $this->createdAt = $announcement->getCreatedAt();
    }


    public function __toString() {
        /** @var string */
        $createdAt = empty($this->createdAt) ? "" : $this->createdAt->format(\DateTime::ISO8601);
        $startDate = empty($this->startDate) ? "" : $this->startDate->format(\DateTime::ISO8601);
        $endDate = empty($this->endDate) ? "" : $this->endDate->format(\DateTime::ISO8601);

        return sprintf(
            "HistoricAnnouncement [id: %d, title: '%s', rentPrice: %d, startDate: '%s', endDate: '%s', createdAt: '%s', location: %s, creator: %s]",
            $this->id, $this->title, $this->rentPrice, $startDate, $endDate, $createdAt, $this->location, $this->creator);
    }


    public function getId() {
        return $this->id;
    }


    public function getTitle() {
        return $this->title;
    }


    public function setTitle(?string $title) {
        $this->title = $title;
        return $this;
    }


    public function getType() {
        return $this->type;
    }


    public function setType(?string $type) {
        $this->type = $type;
        return $this;
    }


    public function getCreator() {
        return $this->creator;
    }


    public function setCreator(User $creator = null) {
        $this->creator = $creator;
        return $this;
    }


    public function getRentPrice() {
        return $this->rentPrice;
    }


    public function setRentPrice(?int $rentPrice) {
        $this->rentPrice = $rentPrice;
        return $this;
    }


    public function getStartDate() {
        return $this->startDate;
    }


    public function setStartDate(\DateTime $startDate = null) {
        $this->startDate = $startDate;
        return $this;
    }


    public function getEndDate() {
        return $this->endDate;
    }


    public function setEndDate(\DateTime $endDate = null) {
        $this->endDate = $endDate;
        return $this;
    }


    public function getLocation() {
        return $this->location;
    }


    public function setLocation(Address $location = null) {
        $this->location = $location;
        return $this;
    }


    public function getCreatedAt() {
        return $this->createdAt;
    }


    public function setCreatedAt(\DateTime $createdAt = null) {
        $this->createdAt = $createdAt;
        return $this;
    }

}