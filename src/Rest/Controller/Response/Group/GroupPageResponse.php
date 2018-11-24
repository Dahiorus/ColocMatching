<?php

namespace App\Rest\Controller\Response\Group;

use App\Core\DTO\Group\GroupDto;
use App\Rest\Controller\Response\PageResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class GroupPageResponse extends PageResponse
{
    /**
     * @var GroupDto[]
     * @SWG\Property(property="content", type="array", @SWG\Items(ref=@Model(type=GroupDto::class)))
     */
    protected $content;
}