<?php

namespace App\Rest\Controller\Response\Announcement;

use App\Core\DTO\Announcement\CommentDto;
use App\Rest\Controller\Response\PageResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class CommentPageResponse extends PageResponse
{
    /**
     * @var CommentDto[]
     * @SWG\Property(property="content", type="array", @SWG\Items(ref=@Model(type=CommentDto::class)))
     */
    protected $content;
}