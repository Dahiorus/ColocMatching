<?php

namespace ColocMatching\CoreBundle\DTO\Announcement;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\Entity\Announcement\Comment;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comment of a announcement
 *
 * @Serializer\ExclusionPolicy("ALL")
 * @Hateoas\Relation(name="author",
 *   href= @Hateoas\Route(name="rest_get_user", absolute=true,
 *     parameters={ "id" = "expr(object.getAuthorId())" }))
 * @SWG\Definition(definition="Comment")
 */
class CommentDto extends AbstractDto
{
    /**
     * Comment message
     * @var string
     * @Serializer\Expose
     * @SWG\Property
     */
    private $message;

    /**
     * Appreciation mark of the announcement
     * @var integer
     *
     * @Serializer\Expose
     * @SWG\Property
     * @Assert\Type(type="integer")
     * @Assert\Range(min="0", max="5")
     */
    private $rate;

    /**
     * @var integer
     */
    private $authorId;


    public function __toString() : string
    {
        return parent::__toString() . "[message = " . $this->message . ", rate = " . $this->rate
            . ", authorId = " . $this->authorId . "]";
    }


    public function getMessage()
    {
        return $this->message;
    }


    public function setMessage(?string $message)
    {
        $this->message = $message;

        return $this;
    }


    public function getRate()
    {
        return $this->rate;
    }


    public function setRate(?int $rate)
    {
        $this->rate = $rate;

        return $this;
    }


    public function getAuthorId()
    {
        return $this->authorId;
    }


    public function setAuthorId(?int $authorId)
    {
        $this->authorId = $authorId;

        return $this;
    }


    public function getEntityClass() : string
    {
        return Comment::class;
    }
}