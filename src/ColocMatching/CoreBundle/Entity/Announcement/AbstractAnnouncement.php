<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Class representing an abstract announcement
 *
 * @ORM\MappedSuperclass
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="AbstractAnnouncement")
 * @Hateoas\Relation(
 *   name= "creator",
 *   href= @Hateoas\Route(name="rest_get_user", absolute=true,
 *     parameters={ "id" = "expr(object.getCreator().getId())" })
 * )
 *
 *
 * @author Dahiorus
 */
abstract class AbstractAnnouncement implements EntityInterface {

    const TYPE_RENT = "rent";

    const TYPE_SUBLEASE = "sublease";

    const TYPE_SHARING = "sharing";

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     * @SWG\Property(description="Announcement ID", readOnly=true)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @JMS\Expose()
     * @SWG\Property(description="Announcement title")
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     * @JMS\Expose()
     * @SWG\Property(description="Announcement type", enum={ "rent", "sublease", "sharing" })
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
     * @JMS\SerializedName("rentPrice")
     * @JMS\Expose()
     * @SWG\Property(description="Announcement rent price")
     */
    protected $rentPrice;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="date")
     * @JMS\Expose()
     * @JMS\SerializedName("startDate")
     * @SWG\Property(description="Announcement start date", format="date")
     */
    protected $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="date", nullable=true)
     * @JMS\Expose()
     * @JMS\SerializedName("endDate")
     * @SWG\Property(description="Announcement end date", format="date")
     */
    protected $endDate;

    /**
     * @var Address
     *
     * @ORM\OneToOne(targetEntity="Address", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="location_id", nullable=false)
     */
    protected $location;

    /**
     * @var Collection
     */
    protected $comments;


    public function __construct(User $creator) {
        $this->creator = $creator;
        $this->comments = new ArrayCollection();
    }


    public function getId() : int {
        return $this->id;
    }


    public function setId(int $id) {
        $this->id = $id;

        return $this;
    }


    public function getTitle() {
        return $this->title;
    }


    public function setTitle(?string $title) {
        $this->title = $title;

        return $this;
    }


    public function getType() {
        return $this->type;
    }


    public function setType(?string $type) {
        $this->type = $type;

        return $this;
    }


    public function getCreator() : User {
        return $this->creator;
    }


    public function setCreator(User $creator = null) {
        $this->creator = $creator;

        return $this;
    }


    public function getRentPrice() {
        return $this->rentPrice;
    }


    public function setRentPrice(?int $rentPrice) {
        $this->rentPrice = $rentPrice;

        return $this;
    }


    public function getStartDate() {
        return $this->startDate;
    }


    public function setStartDate(\DateTime $startDate = null) {
        $this->startDate = $startDate;

        return $this;
    }


    public function getEndDate() {
        return $this->endDate;
    }


    public function setEndDate(\DateTime $endDate = null) {
        $this->endDate = $endDate;

        return $this;
    }


    public function getLocation() {
        return $this->location;
    }


    public function setLocation(Address $location = null) {
        $this->location = $location;

        return $this;
    }


    public function getComments() : Collection {
        return $this->comments;
    }


    public function setComments(Collection $comments) {
        $this->comments = $comments;
    }


    public function addComment(Comment $comment = null) {
        $this->comments->add($comment);

        return $this;
    }


    public function removeComment(Comment $comment = null) {
        $this->comments->removeElement($comment);
    }


    /**
     * Formatted representation of the location
     *
     * @return string
     */
    public function getFormattedAddress() {
        return $this->location->getFormattedAddress();
    }


    /**
     * Short representation of the location
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("shortLocation")
     * @SWG\Property(property="shortLocation", type="string", readOnly=true)
     *
     * @return string
     */
    public function getShortLocation() {
        return $this->location->getShortAddress();
    }
}