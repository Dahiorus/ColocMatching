<?php

namespace ColocMatching\CoreBundle\Tests\Utils\Mock\User;

use Symfony\Component\HttpFoundation\File\File;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;

class ProfilePictureMock {


    public static function createPicture(int $id, File $file, string $name) {
        $picture = new ProfilePicture();

        $picture->setId($id);
        $picture->setFile($file);
        $picture->setName($name);

        return $picture;
    }


    private function __construct() {
        // empty constructor
    }

}