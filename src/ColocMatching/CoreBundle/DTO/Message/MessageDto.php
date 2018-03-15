<?php

namespace ColocMatching\CoreBundle\DTO\Message;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(definition = "Message")
 * @Hateoas\Relation(
 *   name = "author",
 *   href = @Hateoas\Route(name = "rest_get_user", absolute = true,
 *     parameters = { "id" = "expr(object.getAuthorId())" })
 * )
 *
 * @author Dahiorus
 */
abstract class MessageDto extends AbstractDto
{
    /**
     * @var integer
     */
    protected $conversationId;

    /**
     * @var integer
     */
    protected $authorId;

    /**
     * Message content
     * @var string
     * @Serializer\Expose
     * @Assert\Type(type="string")
     * @SWG\Property
     */
    protected $content;

    /**
     * @var integer
     */
    protected $parentId;


    public function __toString() : string
    {
        return parent::__toString() . "[conversationId = " . $this->conversationId . ", authorId = " . $this->authorId
            . ", content = '" . $this->content . "', parentId = " . $this->parentId . "]";
    }


    public function getConversationId()
    {
        return $this->conversationId;
    }


    public function setConversationId(int $conversationId) : MessageDto
    {
        $this->conversationId = $conversationId;

        return $this;
    }


    public function getAuthorId()
    {
        return $this->authorId;
    }


    public function setAuthorId(?int $authorId) : MessageDto
    {
        $this->authorId = $authorId;

        return $this;
    }


    public function getContent()
    {
        return $this->content;
    }


    public function setContent(?string $content) : MessageDto
    {
        $this->content = $content;

        return $this;
    }


    public function getParentId()
    {
        return $this->parentId;
    }


    public function setParentId(?int $parentId) : MessageDto
    {
        $this->parentId = $parentId;

        return $this;
    }

}
