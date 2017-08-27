<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\Document;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * AnnouncementPicture
 *
 * @ORM\Entity()
 * @ORM\Table(name="announcement_picture", indexes={
 *   @ORM\Index(name="announcement_picture_announcement", columns={"announcement_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(
 *   definition="AnnouncementPicture",
 *   allOf={
 *     { "$ref"="#/definitions/Document" }
 *   }
 * )
 */
class AnnouncementPicture extends Document {

    const UPLOAD_ROOT_DIR = "/uploads/pictures/announcements";

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


    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function generatePicturePath() {
        parent::onPreUpload();
    }


    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload() {
        if (!is_dir($this->getAbsoluteUploadDir())) {
            mkdir($this->getAbsoluteUploadDir());
        }

        parent::onUpload();
    }


    /**
     * @ORM\PostRemove()
     */
    public function removePicture() {
        parent::onRemove();

        $fileCount = count(glob($this->getAbsoluteUploadDir() . "/*"));

        if (is_dir($this->getAbsoluteUploadDir()) && ($fileCount == 0)) {
            rmdir($this->getAbsoluteUploadDir());
        }
    }


    protected function getUploadDir() : string {
        return sprintf("%s/%d", self::UPLOAD_ROOT_DIR, $this->announcement->getId());
    }

}
