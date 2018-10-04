<?php

namespace App\Rest\Controller\Response\Message;

use App\Core\DTO\Message\GroupMessageDto;
use App\Rest\Controller\Response\PageResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class GroupMessagePageResponse extends PageResponse
{
    /**
     * @var GroupMessageDto[]
     * @SWG\Property(property="content", type="array", @SWG\Items(ref=@Model(type=GroupMessageDto::class)))
     */
    protected $content;
}