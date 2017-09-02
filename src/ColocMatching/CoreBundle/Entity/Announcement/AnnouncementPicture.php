<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\Picture;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * AnnouncementPicture
 *
 * @ORM\Entity()
 * @ORM\Table(name="announcement_picture", indexes={
 *   @ORM\Index(name="IDX_ANNOUNCEMENT_PICTURE_ANNOUNCEMENT", columns={"announcement_id"})
 * })
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(
 *   definition="AnnouncementPicture",
 *   allOf={
 *     { "$ref"="#/definitions/Picture" }
 *   }
 * )
 * @Hateoas\Relation(name="announcement",
 *   href= @Hateoas\Route(name="rest_get_announcement", absolute=true,
 *     parameters={ "id" = "expr(object.getAnnouncement().getId())" })
 * )
 */
class AnnouncementPicture extends Picture {

    const UPLOAD_ROOT_DIR = "pictures/announcements";

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     * @SWG\Property(description="AnnouncementPicture id", readOnly=true)
     */
    private $id;

    /**
     *
     * @var Announcement
     *
     * @ORM\ManyToOne(targetEntity="Announcement", inversedBy="pictures", fetch="LAZY")
     * @ORM\JoinColumn(name="announcement_id", nullable=false)
     */
    private $announcement;


    public function __construct(Announcement $announcement) {
        $this->announcement = $announcement;
    }


    public function __toString() {
        $lastUpdate = (empty($this->lastUpdate)) ? "" : $this->lastUpdate->format(\DateTime::ISO8601);

        return sprintf("AnnouncementPicture [id: %d, webPath: '%s', lastUpdate: %s, announcement: %s]", $this->id,
            $this->getWebPath(), $lastUpdate, $this->announcement);
    }


    public function getId() : int {
        return $this->id;
    }


    public function setId(int $id) {
        $this->id = $id;

        return $this;
    }


    public function getAnnouncement() {
        return $this->announcement;
    }


    public function setAnnouncement(Announcement $announcement) {
        $this->announcement = $announcement;

        return $this;
    }


    public function getUploadDir() : string {
        return sprintf("%s/%d", self::UPLOAD_ROOT_DIR, $this->announcement->getId());
    }

}
