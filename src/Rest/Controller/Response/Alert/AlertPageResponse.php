<?php

namespace App\Rest\Controller\Response\Alert;

use App\Core\DTO\Alert\AlertDto;
use App\Rest\Controller\Response\PageResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class AlertPageResponse extends PageResponse
{
    /**
     * @var AlertDto[]
     * @SWG\Property(property="content", type="array", @SWG\Items(ref=@Model(type=AlertDto::class)))
     */
    protected $content;

}
