<?php

namespace ColocMatching\CoreBundle\DTO\Group;

use ColocMatching\CoreBundle\DTO\PictureDto;
use ColocMatching\CoreBundle\Entity\Group\GroupPicture;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(definition="GroupPicture", allOf={ @SWG\Schema(ref="#/definitions/Picture") })
 *
 * @author Dahiorus
 */
class GroupPictureDto extends PictureDto
{
    public function getEntityClass() : string
    {
        return GroupPicture::class;
    }
}