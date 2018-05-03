<?php

namespace ColocMatching\CoreBundle\DTO\User;

use ColocMatching\CoreBundle\DTO\PictureDto;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;

class ProfilePictureDto extends PictureDto
{
    public function getEntityClass() : string
    {
        return ProfilePicture::class;
    }
}