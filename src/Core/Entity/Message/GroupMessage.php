<?php

namespace App\Core\Entity\Message;

use App\Core\Entity\Group\Group;
use App\Core\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class GroupMessage representing a message in a group
 *
 * @ORM\Entity(repositoryClass="App\Core\Repository\Message\GroupMessageRepository")
 * @ORM\Table(
 *   name="group_message",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UK_GROUP_MESSAGE_PARENT", columns={ "parent_id" })
 * }, indexes={
 *     @ORM\Index(name="IDX_GRP_MSG_CONVERSATION", columns={ "conversation_id" }),
 *     @ORM\Index(name="IDX_GRP_MSG_GROUP", columns={ "group_id" }),
 *     @ORM\Index(name="IDX_GRP_MSG_AUTHOR", columns={ "author_id" })
 * })
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="group_messages")
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


    public function setGroup(Group $group)
    {
        $this->group = $group;

        return $this;
    }

}
