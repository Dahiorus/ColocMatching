<?php

namespace App\Rest\Controller\Response\Invitation;

use App\Core\DTO\Invitation\InvitationDto;
use App\Rest\Controller\Response\PageResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class InvitationPageResponse extends PageResponse
{
    /**
     * @var InvitationDto[]
     * @SWG\Property(property="content", type="array", @SWG\Items(ref=@Model(type=InvitationDto::class)))
     */
    protected $content;
}