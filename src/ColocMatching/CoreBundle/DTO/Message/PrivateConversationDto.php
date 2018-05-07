<?php

namespace ColocMatching\CoreBundle\DTO\Message;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\Entity\Message\PrivateConversation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\ExclusionPolicy("ALL")
 *
 * @Hateoas\Relation(
 *   name= "firstParticipant",
 *   href= @Hateoas\Route(name="rest_get_private_messages", absolute=true,
 *     parameters={ "id" = "expr(object.getFirstParticipantId())" })
 * )
 * @Hateoas\Relation(
 *   name= "secondParticipant",
 *   href= @Hateoas\Route(name="rest_get_private_messages", absolute=true,
 *     parameters={ "id" = "expr(object.getSecondParticipantId())" })
 * )
 *
 * @author Dahiorus
 */
class PrivateConversationDto extends AbstractDto
{
    /**
     * @var integer
     */
    private $firstParticipantId;

    /**
     * @var integer
     */
    private $secondParticipantId;

    /**
     * @var Collection<PrivateMessageDto>
     */
    private $messages;


    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }


    public function __toString() : string
    {
        return parent::__toString() . "[firstParticipantId = " . $this->firstParticipantId
            . ", secondParticipantId = " . $this->secondParticipantId . "]";
    }


    public function getFirstParticipantId()
    {
        return $this->firstParticipantId;
    }


    public function setFirstParticipantId(?int $firstParticipantId)
    {
        $this->firstParticipantId = $firstParticipantId;

        return $this;
    }


    public function getSecondParticipantId()
    {
        return $this->secondParticipantId;
    }


    public function setSecondParticipantId(?int $secondParticipantId)
    {
        $this->secondParticipantId = $secondParticipantId;

        return $this;
    }


    /**
     * @return Collection
     */
    public function getMessages() : Collection
    {
        return $this->messages;
    }


    /**
     * @param Collection $messages
     *
     * @return PrivateConversationDto
     */
    public function setMessages(Collection $messages = null) : PrivateConversationDto
    {
        $this->messages = $messages;

        return $this;
    }


    public function getEntityClass() : string
    {
        return PrivateConversation::class;
    }

}
