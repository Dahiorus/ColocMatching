<?php

namespace App\Core\DTO\Announcement;

use App\Core\DTO\AbstractDto;
use App\Core\Entity\Announcement\Comment;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comment of a announcement
 *
 * @Serializer\ExclusionPolicy("ALL")
 *
 * @Hateoas\Relation(name="author",
 *   href= @Hateoas\Route(name="rest_get_user", absolute=true,
 *     parameters={ "id" = "expr(object.getAuthorId())" }))
 */
class CommentDto extends AbstractDto
{
    /**
     * Comment message
     * @var string
     *
     * @Assert\NotBlank
     * @Serializer\Expose
     * @SWG\Property(property="message", type="string")
     */
    private $message;

    /**
     * Appreciation mark of the announcement
     * @var integer
     * @Assert\Type(type="integer")
     * @Assert\Range(min="0", max="5")
     * @Serializer\Expose
     * @SWG\Property(property="rate", type="number")
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