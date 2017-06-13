<?php

namespace ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;
use Symfony\Component\HttpFoundation\File\File;

class AnnouncementPictureMock {


    public static function createAnnouncementPicture(int $id, Announcement $announcement, File $file): AnnouncementPicture {
        $picture = new AnnouncementPicture($announcement);

        $picture->setId($id);
        $picture->setFile($file);

        return $picture;
    }


    private function __construct() {
        // empty constructor
    }

}