<?php

namespace ColocMatching\CoreBundle\Entity\User;

use ColocMatching\CoreBundle\Entity\Message\Message;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PrivateMessage representing a message between 2 users
 *
 * @ORM\Entity()
 * @ORM\Table(name="private_message", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="UK_PRIVATE_MESSAGE_PARENT", columns={ "parent_id" })
 * })
 *
 * @author Dahiorus
 */
class PrivateMessage extends Message
{
    /**
     * @var PrivateConversation
     *
     * @ORM\ManyToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\PrivateConversation", fetch="LAZY",
     *     inversedBy="messages")
     * @ORM\JoinColumn(name="conversation_id", nullable=false)
     */
    protected $conversation;

    /**
     * @var Message
     *
     * @ORM\OneToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\PrivateMessage", fetch="LAZY")
     * @ORM\JoinColumn(name="parent_id", nullable=true)
     */
    protected $parent;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User")
     */
    private $recipient;


    public function __construct(User $author, User $recipient)
    {
        parent::__construct($author);

        $this->recipient = $recipient;
    }


    public function getRecipient() : User
    {
        return $this->recipient;
    }


    public function setRecipient(User $recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

}