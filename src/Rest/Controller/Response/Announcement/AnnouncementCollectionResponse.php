<?php

namespace App\Rest\Controller\Response\Announcement;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Rest\Controller\Response\CollectionResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class AnnouncementCollectionResponse extends CollectionResponse
{
    /**
     * @var AnnouncementDto[]
     * @SWG\Property(property="content", type="array", @SWG\Items(ref=@Model(type=AnnouncementDto::class)))
     */
    protected $content;
}