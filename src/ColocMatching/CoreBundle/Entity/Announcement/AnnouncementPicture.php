<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\Picture;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * AnnouncementPicture
 *
 * @ORM\Entity()
 * @ORM\Table(name="announcement_picture", indexes={
 *   @ORM\Index(name="IDX_ANNOUNCEMENT_PICTURE_ANNOUNCEMENT", columns={"announcement_id"})
 * })
 */
class AnnouncementPicture extends Picture
{
    private const UPLOAD_ROOT_DIR = "pictures/announcements";

    /**
     *
     * @var Announcement
     *
     * @ORM\ManyToOne(targetEntity="Announcement", inversedBy="pictures", fetch="LAZY")
     * @ORM\JoinColumn(name="announcement_id", nullable=false)
     */
    private $announcement;


    public function __construct(Announcement $announcement, UploadedFile $file = null)
    {
        parent::__construct($file);
        $this->announcement = $announcement;
    }


    public function getAnnouncement()
    {
        return $this->announcement;
    }


    public function setAnnouncement(Announcement $announcement)
    {
        $this->announcement = $announcement;

        return $this;
    }


    public function getUploadDir() : string
    {
        return sprintf("%s/%d", self::UPLOAD_ROOT_DIR, $this->announcement->getId());
    }

}
