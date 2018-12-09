<?php

namespace App\Core\Entity\Group;

use App\Core\Entity\Picture;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * GroupPicture
 *
 * @ORM\Entity
 * @ORM\Table(name="group_picture")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="group_pictures")
 *
 * @author Dahiorus
 */
class GroupPicture extends Picture
{
    private const UPLOAD_DIR = "pictures/groups";


    public function __construct(UploadedFile $file = null)
    {
        parent::__construct($file);
    }


    public function getUploadDir() : string
    {
        return self::UPLOAD_DIR;
    }

}
