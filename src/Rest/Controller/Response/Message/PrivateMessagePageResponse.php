<?php

namespace App\Rest\Controller\Response\Message;

use App\Core\DTO\Message\PrivateMessageDto;
use App\Rest\Controller\Response\PageResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class PrivateMessagePageResponse extends PageResponse
{
    /**
     * @var PrivateMessageDto[]
     * @SWG\Property(property="content", type="array", @SWG\Items(ref=@Model(type=PrivateMessageDto::class)))
     */
    protected $content;
}