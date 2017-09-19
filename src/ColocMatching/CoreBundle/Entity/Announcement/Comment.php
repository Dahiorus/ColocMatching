<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comment of a announcement
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity
 * @JMS\ExclusionPolicy("ALL")
 * @Hateoas\Relation(name="author",
 *   href= @Hateoas\Route(name="rest_get_user", absolute=true,
 *     parameters={ "id" = "expr(object.getAuthor().getId())" }))
 * @SWG\Definition(definition="Comment")
 */
class Comment implements EntityInterface {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     * @SWG\Property(description="Comment identifier", readOnly=true)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     * @JMS\Expose()
     * @SWG\Property(description="Comment message")
     */
    private $message;

    /**
     * @var integer
     *
     * @ORM\Column(name="rate", type="integer", options={ "default": 0 })
     * @JMS\Expose()
     * @SWG\Property(description="Appreciation mark of the announcement")
     * @Assert\Type(type="integer")
     * @Assert\Range(min="0", max="5")
     */
    private $rate = 0;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="author_id", nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $author;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @JMS\Expose()
     * @JMS\Type("DateTime<'Y-m-d\TH:i:s'>")
     * @SWG\Property(description="Comment creation date", readOnly=true)
     */
    private $createdAt;


    /**
     * Comment constructor.
     *
     * @param User $author The author of the comment
     */
    public function __construct(User $author) {
        $this->author = $author;
        $this->createdAt = new \DateTime();
    }


    public function __toString() : string {
        return "Comment(" . $this->id . ") [message='" . $this->message . "', rate=" . $this->rate . ", createdAt='"
            . $this->createdAt->format(\DateTime::ISO8601) . "']'";
    }


    public function getId() : int {
        return $this->id;
    }


    public function setId(int $id) {
        $this->id = $id;
    }


    public function getMessage() {
        return $this->message;
    }


    public function setMessage(?string $message) {
        $this->message = $message;
    }


    public function getRate() : int {
        return $this->rate;
    }


    public function setRate(?int $rate) {
        $this->rate = $rate;
    }


    public function getAuthor() : User {
        return $this->author;
    }


    public function setAuthor(User $author = null) {
        $this->author = $author;
    }


    public function getCreatedAt() : \DateTime {
        return $this->createdAt;
    }


    public function setCreatedAt(\DateTime $createdAt = null) {
        $this->createdAt = $createdAt;
    }

}