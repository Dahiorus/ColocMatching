<?php

namespace ColocMatching\CoreBundle\DTO\User;

use ColocMatching\CoreBundle\DTO\PictureDto;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(definition="ProfilePictureDto", allOf={ @SWG\Schema(ref="#/definitions/PictureDto") })
 */
class ProfilePictureDto extends PictureDto
{
    public function getEntityClass() : string
    {
        return ProfilePicture::class;
    }
}