<?php

namespace ColocMatching\CoreBundle\Entity\Message;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class representing an abstract message
 *
 * @ORM\MappedSuperclass
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(definition = "Message")
 * @Hateoas\Relation(
 *   name = "author",
 *   href = @Hateoas\Route(name = "rest_get_user", absolute = true,
 *     parameters = { "id" = "expr(object.getAuthor().getId())" })
 * )
 *
 * @author Dahiorus
 */
abstract class Message implements EntityInterface {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose()
     * @SWG\Property(description="Message identifier", readOnly=true)
     */
    protected $id;

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
     * @Serializer\Expose()
     * @Assert\Type(type="string")
     * @SWG\Property(description="Message content")
     */
    protected $content;

    /**
     * @var Message
     */
    protected $parent;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Serializer\Expose()
     * @Serializer\SerializedName("createdAt")
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:s'>")
     * @SWG\Property(description="Creation date time", format="date-time", readOnly=true)
     */
    protected $createdAt;


    public function __construct(User $author) {
        $this->author = $author;
        $this->createdAt = new \DateTime();
    }


    public function __toString() {
        $createdAt = empty($this->createdAt) ? null : $this->createdAt->format(\DateTime::ISO8601);

        return "content='" . $this->content . "', createdAt='" . $createdAt . "''";
    }


    public function getId() : int {
        return $this->id;
    }


    public function setId(int $id) {
        $this->id = $id;

        return $this;
    }


    public function getCreatedAt() : \DateTime {
        return $this->createdAt;
    }


    public function setCreatedAt(\DateTime $createdAt) {
        $this->createdAt = $createdAt;
    }


    public function getAuthor() : User {
        return $this->author;
    }


    public function setAuthor(User $author) {
        $this->author = $author;

        return $this;
    }


    public function getContent() {
        return $this->content;
    }


    public function setContent(?string $content) {
        $this->content = $content;

        return $this;
    }


    public function getParent() {
        return $this->parent;
    }


    public function setParent(Message $parent = null) {
        $this->parent = $parent;

        return $this;
    }

}