<?php

namespace ColocMatching\CoreBundle\DTO\Group;

use ColocMatching\CoreBundle\DTO\PictureDto;
use ColocMatching\CoreBundle\Entity\Group\GroupPicture;

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