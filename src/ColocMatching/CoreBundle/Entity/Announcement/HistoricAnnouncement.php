<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Form\DataTransformer\AddressTypeToAddressTransformer;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Historic announcement created at the deletion of an Announcement
 *
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\Announcement\HistoricAnnouncementRepository")
 * @ORM\Table(name="historic_announcement",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UK_HIST_ANNOUNCEMENT_CREATOR", columns={"creator_id"}),
 *     @ORM\UniqueConstraint(name="UK_HIST_ANNOUNCEMENT_LOCATION", columns={"location_id"})
 * })
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="HistoricAnnouncement",
 *   allOf={
 *     { "$ref"="#/definitions/AbstractAnnouncement" }
 * })
 *
 * @author Dahiorus
 */
class HistoricAnnouncement extends AbstractAnnouncement {

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="creator_id", nullable=false)
     */
    protected $creator;

    /**
     * @var \DateTime
     */
    private $createdAt;


    public function __construct(Announcement $announcement) {
        $this->setType($announcement->getType());
        $this->setTitle($announcement->getTitle());
        $this->setCreator($announcement->getCreator());
        $this->setRentPrice($announcement->getRentPrice());
        $this->setStartDate($announcement->getStartDate());
        $this->setEndDate($announcement->getEndDate());
        $this->setCreatedAt($announcement->getCreatedAt());

        $transformer = new AddressTypeToAddressTransformer();
        $this->setLocation($transformer->reverseTransform($announcement->getLocation()->getFormattedAddress()));
    }


    public function __toString() {
        /** @var string */
        $createdAt = empty($this->createdAt) ? "" : $this->createdAt->format(\DateTime::ISO8601);
        $startDate = empty($this->startDate) ? "" : $this->startDate->format(\DateTime::ISO8601);
        $endDate = empty($this->endDate) ? "" : $this->endDate->format(\DateTime::ISO8601);

        return sprintf(
            "HistoricAnnouncement [id: %d, title: '%s', rentPrice: %d, startDate: '%s', endDate: '%s', createdAt: '%s', location: %s, creator: %s]",
            $this->id, $this->title, $this->rentPrice, $startDate, $endDate, $createdAt, $this->location,
            $this->creator);
    }


    public function getCreatedAt() {
        return $this->createdAt;
    }


    public function setCreatedAt(\DateTime $createdAt = null) {
        $this->createdAt = $createdAt;

        return $this;
    }

}