<?php

namespace App\Core\Entity\User;

use App\Core\Entity\Picture;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * ProfilePicture
 *
 * @ORM\Entity
 * @ORM\Table(name="user_picture")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="profile_pictures")
 */
class ProfilePicture extends Picture
{
    private const UPLOAD_DIR = "pictures/users";


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
