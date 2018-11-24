<?php

namespace App\Rest\Controller\Response\User;

use App\Core\DTO\User\UserDto;
use App\Rest\Controller\Response\CollectionResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class UserCollectionResponse extends CollectionResponse
{
    /**
     * @var UserDto[]
     * @SWG\Property(property="content", type="array", @SWG\Items(ref=@Model(type=UserDto::class)))
     */
    protected $content;
}