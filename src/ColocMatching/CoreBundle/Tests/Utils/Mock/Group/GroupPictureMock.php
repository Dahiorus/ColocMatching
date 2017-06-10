<?php

namespace ColocMatching\CoreBundle\Tests\Utils\Mock\Group;

use ColocMatching\CoreBundle\Entity\Group\GroupPicture;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GroupPictureMock {


    public static function createPicture(int $id, UploadedFile $file, string $name): GroupPicture {
        $picture = new GroupPicture();

        $picture->setId($id);
        $picture->setFile($file);
        $picture->setName($name);

        return $picture;
    }


    private function __construct() {
        // empty constructor
    }

}