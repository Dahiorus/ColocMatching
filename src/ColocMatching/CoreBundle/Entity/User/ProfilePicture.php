<?php

namespace ColocMatching\CoreBundle\Entity\User;

use ColocMatching\CoreBundle\Entity\Picture;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * ProfilePicture
 *
 * @ORM\Entity()
 * @ORM\Table(name="profile_picture")
 */
class ProfilePicture extends Picture
{
    const UPLOAD_DIR = "pictures/users";


    /**
     * ProfilePicture constructor.
     *
     * @param UploadedFile|null $file
     */
    public function __construct(UploadedFile $file = null)
    {
        parent::__construct($file);
    }


    /**
     * @inheritdoc
     */
    public function getUploadDir() : string
    {
        return self::UPLOAD_DIR;
    }

}