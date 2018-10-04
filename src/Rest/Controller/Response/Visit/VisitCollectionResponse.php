<?php

namespace App\Rest\Controller\Response\Visit;

use App\Core\DTO\Visit\VisitDto;
use App\Rest\Controller\Response\CollectionResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class VisitCollectionResponse extends CollectionResponse
{
    /**
     * @var VisitDto[]
     * @SWG\Property(property="content", type="array", @SWG\Items(ref=@Model(type=VisitDto::class)))
     */
    protected $content;
}