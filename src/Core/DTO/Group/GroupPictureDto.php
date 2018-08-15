<?php

namespace App\Core\DTO\Group;

use App\Core\DTO\PictureDto;
use App\Core\Entity\Group\GroupPicture;

/**
 * @author Dahiorus
 */
class GroupPictureDto extends PictureDto
{
    public function getEntityClass() : string
    {
        return GroupPicture::class;
    }
}