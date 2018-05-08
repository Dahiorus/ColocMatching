<?php

namespace ColocMatching\CoreBundle\Entity\Message;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class GroupMessage representing a message in a group
 *
 * @ORM\Entity
 * @ORM\Table(name="group_message", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="UK_GROUP_MESSAGE_PARENT", columns={ "parent_id" })
 * })
 *
 * @author Dahiorus
 */
class GroupMessage extends Message
{
    /**
     * @var GroupConversation
     *
     * @ORM\ManyToOne(targetEntity=GroupConversation::class, fetch="LAZY", inversedBy="messages")
     * @ORM\JoinColumn(name="conversation_id", nullable=false)
     */
    protected $conversation;

    /**
     * @var GroupMessage
     *
     * @ORM\OneToOne(targetEntity=GroupMessage::class, fetch="LAZY")
     * @ORM\JoinColumn(name="parent_id", nullable=true)
     */
    protected $parent;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity=Group::class, fetch="LAZY")
     * @ORM\JoinColumn(name="group_id", nullable=false)
     */
    private $group;


    public function __construct(User $author, Group $group)
    {
        parent::__construct($author);

        $this->group = $group;
    }


    public function getGroup()
    {
        return $this->group;
    }


    public function setGroup(Group $group = null)
    {
        $this->group = $group;

        return $this;
    }

}
