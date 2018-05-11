<?php

namespace ColocMatching\CoreBundle\Entity\Message;

use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PrivateConversation
 *
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\Message\PrivateConversationRepository")
 * @ORM\Table(name="private_conversation", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="UK_CONVERSATION_PARTICIPANTS",
 *     columns={ "first_participant_id", "second_participant_id" })
 * })
 *
 * @author Dahiorus
 */
class PrivateConversation extends Conversation
{
    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity=PrivateMessage::class, fetch="EXTRA_LAZY",
     *   cascade={ "persist", "remove" }, orphanRemoval=true, mappedBy="conversation")
     * @ORM\OrderBy(value={ "createdAt" = "ASC" })
     */
    protected $messages;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity=User::class, fetch="LAZY")
     * @ORM\JoinColumn(name="first_participant_id", nullable=false)
     */
    private $firstParticipant;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity=User::class, fetch="LAZY")
     * @ORM\JoinColumn(name="second_participant_id", nullable=false)
     */
    private $secondParticipant;


    public function __construct(User $firstParticipant, User $secondParticipant)
    {
        parent::__construct();

        $this->firstParticipant = $firstParticipant;
        $this->secondParticipant = $secondParticipant;
    }


    public function getFirstParticipant() : User
    {
        return $this->firstParticipant;
    }


    public function setFirstParticipant(User $firstParticipant)
    {
        $this->firstParticipant = $firstParticipant;

        return $this;
    }


    public function getSecondParticipant() : User
    {
        return $this->secondParticipant;
    }


    public function setSecondParticipant(User $secondParticipant)
    {
        $this->secondParticipant = $secondParticipant;

        return $this;
    }

}
