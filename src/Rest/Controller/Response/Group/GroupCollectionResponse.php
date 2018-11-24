<?php

namespace App\Rest\Controller\Response\Group;

use App\Core\DTO\Group\GroupDto;
use App\Rest\Controller\Response\CollectionResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class GroupCollectionResponse extends CollectionResponse
{
    /**
     * @var GroupDto[]
     * @SWG\Property(property="content", type="array", @SWG\Items(ref=@Model(type=GroupDto::class)))
     */
    protected $content;
}