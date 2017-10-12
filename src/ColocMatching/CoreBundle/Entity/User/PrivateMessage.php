<?php

namespace ColocMatching\CoreBundle\Entity\User;

use ColocMatching\CoreBundle\Entity\Message\Message;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * Class PrivateMessage representing a message between 2 users
 *
 * @ORM\Entity()
 * @ORM\Table(name="private_message", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="UK_PRIVATE_MESSAGE_PARENT", columns={ "parent_id" })
 * })
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="PrivateMessage", allOf={
 *   "$ref" = "#/definitions/Message"
 * })
 * @Hateoas\Relation(
 *   name= "recipient",
 *   href= @Hateoas\Route(name="rest_get_user", absolute=true,
 *     parameters={ "id" = "expr(object.getRecipient().getId())" })
 * )
 */
class PrivateMessage extends Message {

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


    public function __construct(User $author, User $recipient) {
        parent::__construct($author);

        $this->recipient = $recipient;
    }


    public function __toString() {
        return "PrivateMessage(" . $this->id . ") [" . parent::__toString() . "]";
    }


    public function getRecipient() : User {
        return $this->recipient;
    }


    public function setRecipient(User $recipient) {
        $this->recipient = $recipient;

        return $this;
    }

}