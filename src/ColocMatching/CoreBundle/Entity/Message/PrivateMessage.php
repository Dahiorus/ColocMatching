<?php

namespace ColocMatching\CoreBundle\Entity\Message;

use ColocMatching\CoreBundle\Entity\User\User;
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
     * @ORM\ManyToOne(targetEntity=PrivateConversation::class, fetch="LAZY",
     *     inversedBy="messages")
     * @ORM\JoinColumn(name="conversation_id", nullable=false)
     */
    protected $conversation;

    /**
     * @var Message
     *
     * @ORM\OneToOne(targetEntity=PrivateMessage::class, fetch="LAZY")
     * @ORM\JoinColumn(name="parent_id", nullable=true)
     */
    protected $parent;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User")
     * @ORM\JoinColumn(name="recipient_id", nullable=false)
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
