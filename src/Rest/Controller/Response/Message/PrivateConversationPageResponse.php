<?php

namespace App\Rest\Controller\Response\Message;

use App\Core\DTO\Message\PrivateConversationDto;
use App\Rest\Controller\Response\PageResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class PrivateConversationPageResponse extends PageResponse
{
    /**
     * @var PrivateConversationDto[]
     * @SWG\Property(property="content", type="array", @SWG\Items(ref=@Model(type=PrivateConversationDto::class)))
     */
    protected $content;
}