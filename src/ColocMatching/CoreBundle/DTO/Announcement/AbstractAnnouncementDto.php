<?php

namespace ColocMatching\CoreBundle\DTO\Announcement;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\Entity\Announcement\AbstractAnnouncement;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(
 *   definition="AbstractAnnouncement", required={ "title", "type", "rentPrice", "startDate", "location" },
 *   allOf={ @SWG\Schema(ref="#/definitions/AbstractDto") })
 * @Hateoas\Relation(
 *   name= "creator", href= @Hateoas\Route(name="rest_get_user", absolute=true,
 *     parameters={ "id" = "expr(object.getCreatorId())" })
 * )
 *
 * @author Dahiorus
 */
abstract class AbstractAnnouncementDto extends AbstractDto
{
    /**
     * Announcement title
     * @var string
     * @Assert\NotBlank
     * @Serializer\Expose
     * @SWG\Property
     */
    protected $title;

    /**
     * Announcement type
     * @var string
     * @Assert\NotBlank
     * @Assert\Choice(choices={ AbstractAnnouncement::TYPE_RENT, AbstractAnnouncement::TYPE_SHARING,
     *     AbstractAnnouncement::TYPE_SUBLEASE }, strict=true)
     * @Serializer\Expose
     * @SWG\Property(enum={ "rent", "sublease", "sharing" }, required=true)
     */
    protected $type;

    /**
     * Announcement rent price
     * @var integer
     * @Assert\NotNull
     * @Assert\GreaterThan(0)
     * @Serializer\SerializedName("rentPrice")
     * @Serializer\Expose
     * @SWG\Property
     */
    protected $rentPrice;

    /**
     * Announcement start date
     * @var \DateTime
     * @Assert\NotNull
     * @Serializer\Expose
     * @Serializer\SerializedName("startDate")
     * @SWG\Property(format="date")
     */
    protected $startDate;

    /**
     * Announcement end date
     * @var \DateTime
     * @Serializer\Expose
     * @Serializer\SerializedName("endDate")
     * @SWG\Property(format="date")
     */
    protected $endDate;

    /**
     * Announcement formatted address location
     * @var string
     * @Assert\NotBlank
     * @Serializer\Expose
     * @SWG\Property
     */
    protected $location;

    /**
     * Announcement creator unique identifier
     * @var integer
     */
    protected $creatorId;


    public function __toString() : string
    {
        $startDate = empty($this->startDate) ? null : $this->startDate->format(\DateTime::ISO8601);
        $endDate = empty($this->endDate) ? null : $this->endDate->format(\DateTime::ISO8601);

        return parent::__toString() . "[title = " . $this->title . ", type = " . $this->type
            . ", creatorId = " . $this->creatorId . ", rentPrice = " . $this->rentPrice . ", startDate = " . $startDate
            . ", endDate = " . $endDate . ", location = " . $this->location . "]";
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @param string $title
     *
     * @return AbstractAnnouncementDto
     */
    public function setTitle(?string $title)
    {
        $this->title = $title;

        return $this;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @param string $type
     *
     * @return AbstractAnnouncementDto
     */
    public function setType(?string $type)
    {
        $this->type = $type;

        return $this;
    }


    /**
     * @return int
     */
    public function getRentPrice()
    {
        return $this->rentPrice;
    }


    /**
     * @param int $rentPrice
     *
     * @return AbstractAnnouncementDto
     */
    public function setRentPrice(?int $rentPrice) : AbstractAnnouncementDto
    {
        $this->rentPrice = $rentPrice;

        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }


    /**
     * @param \DateTime $startDate
     *
     * @return AbstractAnnouncementDto
     */
    public function setStartDate(\DateTime $startDate = null) : AbstractAnnouncementDto
    {
        $this->startDate = $startDate;

        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }


    /**
     * @param \DateTime $endDate
     *
     * @return AbstractAnnouncementDto
     */
    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;

        return $this;
    }


    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }


    /**
     * @param string $location
     *
     * @return AbstractAnnouncementDto
     */
    public function setLocation(?string $location)
    {
        $this->location = $location;

        return $this;
    }


    /**
     * @return int
     */
    public function getCreatorId()
    {
        return $this->creatorId;
    }


    /**
     * @param int $creatorId
     *
     * @return AbstractAnnouncementDto
     */
    public function setCreatorId(?int $creatorId) : AbstractAnnouncementDto
    {
        $this->creatorId = $creatorId;

        return $this;
    }

}
