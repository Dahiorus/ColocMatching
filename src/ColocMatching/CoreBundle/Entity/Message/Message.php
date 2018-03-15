<?php

namespace ColocMatching\CoreBundle\Entity\Message;

use ColocMatching\CoreBundle\Entity\AbstractEntity;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class representing an abstract message
 *
 * @ORM\MappedSuperclass
 *
 * @author Dahiorus
 */
abstract class Message extends AbstractEntity
{
    /**
     * @var Conversation
     */
    protected $conversation;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User", cascade={ "persist" }, fetch="LAZY")
     * @ORM\JoinColumn(name="author_id", nullable=false)
     */
    protected $author;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    protected $content;

    /**
     * @var Message
     */
    protected $parent;


    public function __construct(User $author)
    {
        $this->author = $author;
    }


    public function __toString()
    {
        return parent::__toString() . "[content = '" . $this->content . "']";
    }


    public function getConversation()
    {
        return $this->conversation;
    }


    public function setConversation(Conversation $conversation = null)
    {
        $this->conversation = $conversation;

        return $this;
    }


    public function getAuthor() : User
    {
        return $this->author;
    }


    public function setAuthor(User $author)
    {
        $this->author = $author;

        return $this;
    }


    public function getContent()
    {
        return $this->content;
    }


    public function setContent(?string $content)
    {
        $this->content = $content;

        return $this;
    }


    public function getParent()
    {
        return $this->parent;
    }


    public function setParent(Message $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

}