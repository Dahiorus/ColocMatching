<?php

namespace ColocMatching\CoreBundle\Entity\User;

use ColocMatching\CoreBundle\Entity\Message\Conversation;
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
 */
class PrivateConversation extends Conversation {

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="ColocMatching\CoreBundle\Entity\User\PrivateMessage", fetch="EAGER",
     *   cascade={ "persist", "remove" })
     * @ORM\JoinTable(name="private_conversation_message",
     *   joinColumns={ @ORM\JoinColumn(name="conversation_id") },
     *   inverseJoinColumns={ @ORM\JoinColumn(name="message_id", unique=true) })
     * @ORM\OrderBy(value={ "createdAt" = "ASC" })
     */
    protected $messages;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="first_participant_id", nullable=false)
     */
    private $firstParticipant;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="second_participant_id", nullable=false)
     */
    private $secondParticipant;


    public function __construct(User $firstParticipant, User $secondParticipant) {
        parent::__construct();

        $this->firstParticipant = $firstParticipant;
        $this->secondParticipant = $secondParticipant;
    }


    /**
     * @return User
     */
    public function getFirstParticipant() : User {
        return $this->firstParticipant;
    }


    public function setFirstParticipant(User $firstParticipant) {
        $this->firstParticipant = $firstParticipant;

        return $this;
    }


    public function getSecondParticipant() : User {
        return $this->secondParticipant;
    }


    public function setSecondParticipant(User $secondParticipant) {
        $this->secondParticipant = $secondParticipant;

        return $this;
    }

}
