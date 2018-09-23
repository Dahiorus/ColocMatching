<?php

namespace App\Core\DTO\Announcement;

use App\Core\DTO\PictureDto;
use App\Core\Entity\Announcement\AnnouncementPicture;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\ExclusionPolicy("ALL")
 *
 * @author Dahiorus
 */
class AnnouncementPictureDto extends PictureDto
{
    /**
     * @var integer
     */
    private $announcementId;


    public function getEntityClass() : string
    {
        return AnnouncementPicture::class;
    }


    public function getAnnouncementId()
    {
        return $this->announcementId;
    }


    public function setAnnouncementId(?int $announcementId)
    {
        $this->announcementId = $announcementId;

        return $this;
    }
}
