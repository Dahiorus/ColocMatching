<?php

namespace App\Core\Entity\Announcement;

use App\Core\Entity\AbstractEntity;
use App\Core\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class representing an abstract announcement
 *
 * @ORM\MappedSuperclass
 *
 * @author Dahiorus
 */
abstract class AbstractAnnouncement extends AbstractEntity
{
    const TYPE_RENT = "rent";

    const TYPE_SUBLEASE = "sublease";

    const TYPE_SHARING = "sharing";

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    protected $type;

    /**
     * @var User
     */
    protected $creator;

    /**
     * @var integer
     *
     * @ORM\Column(name="rent_price", type="integer")
     */
    protected $rentPrice;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="date")
     */
    protected $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="date", nullable=true)
     */
    protected $endDate;

    /**
     * @var Address
     *
     * @ORM\Embedded(class = "App\Core\Entity\Announcement\Address")
     */
    protected $location;

    /**
     * @var Collection
     */
    protected $comments;


    public function __construct(User $creator)
    {
        $this->creator = $creator;
        $this->comments = new ArrayCollection();
    }


    public function __toString() : string
    {
        $startDate = empty($this->startDate) ? null : $this->startDate->format(\DateTime::ISO8601);
        $endDate = empty($this->endDate) ? null : $this->endDate->format(\DateTime::ISO8601);

        return parent::__toString() . "[title = " . $this->title . ", type = " . $this->type
            . ", rentPrice = " . $this->rentPrice . ", startDate = " . $startDate . ", endDate = " . $endDate
            . ", location = " . $this->location . "]";
    }


    public function getTitle()
    {
        return $this->title;
    }


    public function setTitle(?string $title)
    {
        $this->title = $title;

        return $this;
    }


    public function getType()
    {
        return $this->type;
    }


    public function setType(?string $type)
    {
        $this->type = $type;

        return $this;
    }


    public function getCreator() : User
    {
        return $this->creator;
    }


    public function setCreator(User $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }


    public function getRentPrice()
    {
        return $this->rentPrice;
    }


    public function setRentPrice(?int $rentPrice)
    {
        $this->rentPrice = $rentPrice;

        return $this;
    }


    public function getStartDate()
    {
        return $this->startDate;
    }


    public function setStartDate(\DateTime $startDate = null)
    {
        $this->startDate = $startDate;

        return $this;
    }


    public function getEndDate()
    {
        return $this->endDate;
    }


    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;

        return $this;
    }


    public function getLocation()
    {
        return $this->location;
    }


    public function setLocation(Address $location = null)
    {
        $this->location = $location;

        return $this;
    }


    public function getComments() : Collection
    {
        return $this->comments;
    }


    public function setComments(Collection $comments)
    {
        $this->comments = $comments;
    }


    public function addComment(Comment $comment = null)
    {
        $this->comments->add($comment);

        return $this;
    }


    public function removeComment(Comment $comment = null)
    {
        $this->comments->removeElement($comment);
    }

}