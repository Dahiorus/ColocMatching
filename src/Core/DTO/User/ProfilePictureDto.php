<?php

namespace App\Core\DTO\User;

use App\Core\DTO\PictureDto;
use App\Core\Entity\User\ProfilePicture;

class ProfilePictureDto extends PictureDto
{
    public function getEntityClass() : string
    {
        return ProfilePicture::class;
    }
}