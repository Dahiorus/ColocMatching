<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Historic announcement created at the deletion of an Announcement
 *
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\Announcement\HistoricAnnouncementRepository")
 * @ORM\Table(name="historic_announcement")
 *
 * @author Dahiorus
 */
class HistoricAnnouncement extends AbstractAnnouncement
{
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
     *
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;


    /**
     * Creates a historic announcement from the announcement
     *
     * @param Announcement $announcement The announcement
     *
     * @return HistoricAnnouncement
     */
    public static function create(Announcement $announcement) : HistoricAnnouncement
    {
        $historicAnnouncement = new self($announcement->getCreator());

        $historicAnnouncement->setType($announcement->getType());
        $historicAnnouncement->setTitle($announcement->getTitle());
        $historicAnnouncement->setRentPrice($announcement->getRentPrice());
        $historicAnnouncement->setStartDate($announcement->getStartDate());
        $historicAnnouncement->setEndDate($announcement->getEndDate());
        $historicAnnouncement->setCreationDate($announcement->getCreatedAt());
        $historicAnnouncement->setComments($announcement->getComments());
        $historicAnnouncement->setLocation($announcement->getLocation());

        return $historicAnnouncement;
    }


    public function __toString() : string
    {
        $creationDate = empty($this->creationDate) ? null : $this->creationDate->format(DATE_ISO8601);

        return parent::__toString() . "[creationDate = " . $creationDate . "]";
    }


    public function getCreationDate()
    {
        return $this->creationDate;
    }


    public function setCreationDate(\DateTime $creationDate = null)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

}
