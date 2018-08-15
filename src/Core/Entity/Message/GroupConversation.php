<?php

namespace App\Core\Entity\Message;

use App\Core\Entity\Group\Group;
use App\Core\Repository\Message\GroupConversationRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PrivateConversation
 *
 * @ORM\Entity(repositoryClass=GroupConversationRepository::class)
 * @ORM\Table(
 *   name="group_conversation",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UK_CONVERSATION_GROUP", columns={ "group_id" })
 * }, indexes={
 *     @ORM\Index(name="IDX_GRP_CONVERSATION", columns={ "group_id" })
 * })
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="group_conversations")
 *
 * @author Dahiorus
 */
class GroupConversation extends Conversation
{
    /**
     * @var Collection<GroupMessage>
     *
     * @ORM\OneToMany(
     *   targetEntity=GroupMessage::class, fetch="EXTRA_LAZY", cascade={ "persist", "remove" }, orphanRemoval=true,
     *   mappedBy="conversation")
     * @ORM\OrderBy(value={ "createdAt" = "ASC" })
     */
    protected $messages;

    /**
     * @var Group
     *
     * @ORM\OneToOne(targetEntity=Group::class, fetch="LAZY")
     * @ORM\JoinColumn(name="group_id", nullable=false)
     */
    private $group;


    public function __construct(Group $group)
    {
        parent::__construct();

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