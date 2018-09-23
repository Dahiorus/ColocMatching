<?php

namespace App\Core\Entity\Announcement;

use App\Core\Entity\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Historic announcement created at the deletion of an Announcement
 *
 * @ORM\Entity(repositoryClass="App\Core\Repository\Announcement\HistoricAnnouncementRepository")
 * @ORM\Table(
 *   name="historic_announcement",
 *   indexes={
 *     @ORM\Index(name="IDX_HIST_ANNOUNCEMENT_TYPE", columns={ "type" }),
 *     @ORM\Index(
 *       name="IDX_HIST_ANNOUNCEMENT_LOCATION",
 *       columns={ "location_route", "location_locality", "location_country", "location_zip_code" }),
 *     @ORM\Index(name="IDX_HIST_ANNOUNCEMENT_RENT_PRICE", columns={ "rent_price" }),
 *     @ORM\Index(name="IDX_HIST_ANNOUNCEMENT_START_DATE", columns={ "start_date" }),
 *     @ORM\Index(name="IDX_HIST_ANNOUNCEMENT_END_DATE", columns={ "end_date" }),
 *     @ORM\Index(name="IDX_HIST_ANNOUNCEMENT_CREATOR", columns={ "creator_id" })
 * })
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="historic_announcements")
 *
 * @author Dahiorus
 */
class HistoricAnnouncement extends AbstractAnnouncement
{
    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Core\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="creator_id", nullable=false)
     */
    protected $creator;

    /**
     * @var Collection<Comment>
     *
     * @ORM\ManyToMany(targetEntity="Comment", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="historic_announcement_comment",
     *   joinColumns={ @ORM\JoinColumn(name="announcement_id", nullable=false) },
     *   inverseJoinColumns={ @ORM\JoinColumn(name="comment_id", unique=true, nullable=false) })
     * @ORM\OrderBy({ "createdAt" = "DESC" })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="hist_announcement_comments")
     */
    protected $comments;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(name="creation_date", type="datetime_immutable")
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


    public function setCreationDate(\DateTimeImmutable $creationDate = null)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

}
