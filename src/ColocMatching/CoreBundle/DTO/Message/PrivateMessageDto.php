<?php

namespace ColocMatching\CoreBundle\DTO\Message;

use ColocMatching\CoreBundle\Entity\User\PrivateMessage;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="PrivateMessage", allOf={ @SWG\Schema(ref="#/definitions/Message") })
 * @Hateoas\Relation(
 *   name= "recipient",
 *   href= @Hateoas\Route(name="rest_get_user", absolute=true,
 *     parameters={ "id" = "expr(object.getRecipientId())" })
 * )
 *
 * @author Dahiorus
 */
class PrivateMessageDto extends MessageDto
{
    /**
     * @var integer
     */
    private $recipientId;


    public function __toString() : string
    {
        return parent::__toString() . "[recipientId = " . $this->recipientId . "]";
    }


    public function getRecipientId()
    {
        return $this->recipientId;
    }


    public function setRecipientId(int $recipientId)
    {
        $this->recipientId = $recipientId;

        return $this;
    }


    public function getEntityClass() : string
    {
        return PrivateMessage::class;
    }

}
