<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\AbstractEntity;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Comment of a announcement
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="comments")
 *
 * @author Dahiorus
 */
class Comment extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
     * @var integer
     *
     * @ORM\Column(name="rate", type="integer", options={ "default": 0 })
     */
    private $rate = 0;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="author_id", nullable=false, onDelete="CASCADE")
     */
    private $author;


    /**
     * Comment constructor.
     *
     * @param User $author The author of the comment
     */
    public function __construct(User $author)
    {
        $this->author = $author;
        $this->createdAt = new \DateTime();
    }


    public function __toString() : string
    {
        return parent::__toString() . "[message = " . $this->message . ", rate = " . $this->rate . "]";
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


    public function getRate() : int
    {
        return $this->rate;
    }


    public function setRate(?int $rate)
    {
        $this->rate = $rate;

        return $this;
    }


    public function getAuthor() : User
    {
        return $this->author;
    }


    public function setAuthor(User $author = null)
    {
        $this->author = $author;

        return $this;
    }

}
