<?php

namespace App\Rest\Controller\Response\Announcement;

use App\Core\DTO\Announcement\HistoricAnnouncementDto;
use App\Rest\Controller\Response\CollectionResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class HistoricAnnouncementCollectionResponse extends CollectionResponse
{
    /**
     * @var HistoricAnnouncementDto[]
     * @SWG\Property(property="content", type="array", @SWG\Items(ref=@Model(type=HistoricAnnouncementDto::class)))
     */
    protected $content;
}