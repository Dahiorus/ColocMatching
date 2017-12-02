<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Historic announcement created at the deletion of an Announcement
 *
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\Announcement\HistoricAnnouncementRepository")
 * @ORM\Table(name="historic_announcement")
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="HistoricAnnouncement",
 *   allOf={
 *     { "$ref"="#/definitions/AbstractAnnouncement" }
 * })
 * @Hateoas\Relation(
 *   name="self",
 *   href= @Hateoas\Route(name="rest_get_historic_announcement", absolute=true,
 *     parameters={ "id" = "expr(object.getId())" })
 * )
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
     * @var Collection<Comment>
     *
     * @ORM\ManyToMany(targetEntity="Comment", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="historic_announcement_comment",
     *   joinColumns={ @ORM\JoinColumn(name="announcement_id", unique=true, nullable=false) },
     *   inverseJoinColumns={ @ORM\JoinColumn(name="comment_id", nullable=false) })
     */
    protected $comments;

    /**
     * @var \DateTime
     */
    private $createdAt;


    public function __construct(Announcement $announcement) {
        parent::__construct($announcement->getCreator());

        $this->setType($announcement->getType());
        $this->setTitle($announcement->getTitle());
        $this->setRentPrice($announcement->getRentPrice());
        $this->setStartDate($announcement->getStartDate());
        $this->setEndDate($announcement->getEndDate());
        $this->setCreatedAt($announcement->getCreatedAt());
        $this->setComments($announcement->getComments());
        $this->setLocation($announcement->getLocation());
    }


    public function __toString() {
        $createdAt = empty($this->createdAt) ? null : $this->createdAt->format(\DateTime::ISO8601);
        $startDate = empty($this->startDate) ? null : $this->startDate->format(\DateTime::ISO8601);
        $endDate = empty($this->endDate) ? null : $this->endDate->format(\DateTime::ISO8601);

        return "HistoricAnnouncement(" . $this->id . ") [title='" . $this->title . "', startDate='" . $startDate
            . "', endDate='" . $endDate . "', createdAt='" . $createdAt . "']";
    }


    public function getCreatedAt() {
        return $this->createdAt;
    }


    public function setCreatedAt(\DateTime $createdAt = null) {
        $this->createdAt = $createdAt;

        return $this;
    }

}