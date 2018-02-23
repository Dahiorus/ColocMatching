<?php

namespace ColocMatching\CoreBundle\DTO\User;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\AnnouncementPreference;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

class AnnouncementPreferenceDto extends AbstractDto
{
    /**
     * @var integer
     */
    private $addressId;

    /**
     * Search area location filter
     *
     * @var string
     *
     * @Serializer\Expose
     * @SWG\Property(example="Paris 75001")
     */
    private $address;

    /**
     * Rent price start range filter
     *
     * @var integer
     *
     * @Assert\GreaterThanOrEqual(0)
     * @Serializer\SerializedName("rentPriceStart")
     * @Serializer\Expose
     * @SWG\Property(example="300")
     */
    private $rentPriceStart;

    /**
     * Rent price end range filter
     *
     * @var integer
     *
     * @Assert\GreaterThanOrEqual(0)
     * @Serializer\SerializedName("rentPriceEnd")
     * @Serializer\Expose
     * @SWG\Property(example="1500")
     */
    private $rentPriceEnd;

    /**
     * @var array
     *
     * @Assert\Choice(choices={ Announcement::TYPE_RENT, Announcement::TYPE_SUBLEASE, Announcement::TYPE_SHARING },
     *   multiple=true, strict=true)
     * @Serializer\Expose
     * @SWG\Property(description="Announcement types filter", enum={ "rent", "sublease", "sharing" },
     *   @SWG\Items(type="string"))
     */
    private $types = array ();

    /**
     * @var \DateTime
     *
     * @Assert\Date
     * @Serializer\SerializedName("startDateAfter")
     * @Serializer\Expose
     * @SWG\Property(description="Start date 'from' filter", format="date")
     */
    private $startDateAfter;

    /**
     * @var \DateTime
     *
     * @Assert\Date
     * @Serializer\SerializedName("startDateBefore")
     * @Serializer\Expose
     * @SWG\Property(description="Start date 'to' filter", format="date")
     */
    private $startDateBefore;

    /**
     * @var \DateTime
     *
     * @Assert\Date
     * @Serializer\SerializedName("endDateAfter")
     * @Serializer\Expose
     * @SWG\Property(description="End date 'from' filter", format="date")
     */
    private $endDateAfter;

    /**
     * @var \DateTime
     *
     * @Assert\Date
     * @Serializer\SerializedName("endDateBefore")
     * @Serializer\Expose
     * @SWG\Property(description="End date 'to' filter", format="date")
     */
    private $endDateBefore;

    /**
     * @var boolean
     *
     * @Serializer\SerializedName("withPictures")
     * @Serializer\Expose
     * @SWG\Property(description="Only announcements with pictures")
     */
    private $withPictures = false;


    public function __toString() : string
    {
        $startDateAfter = empty($this->startDateAfter) ? null : $this->startDateAfter->format(\DateTime::ISO8601);
        $startDateBefore = empty($this->startDateBefore) ? null : $this->startDateBefore->format(\DateTime::ISO8601);
        $endDateAfter = empty($this->endDateAfter) ? null : $this->endDateAfter->format(\DateTime::ISO8601);
        $endDateBefore = empty($this->endDateBefore) ? null : $this->endDateBefore->format(\DateTime::ISO8601);

        return parent::__toString() . "[address = " . $this->address . ", rentPriceStart = " . $this->rentPriceStart
            . ", rentPriceEnd = " . $this->rentPriceEnd . ", types = " . implode(", ", $this->types)
            . ", startDateAfter = " . $startDateAfter . ", startDateBefore = " . $startDateBefore
            . ", endDateAfter = " . $endDateAfter . ", endDateBefore = " . $endDateBefore
            . ", withPictures = " . $this->withPictures . "]";
    }


    public function getAddressId()
    {
        return $this->addressId;
    }


    public function setAddressId(?int $addressId)
    {
        $this->addressId = $addressId;

        return $this;
    }


    public function getAddress()
    {
        return $this->address;
    }


    public function setAddress(?string $address)
    {
        $this->address = $address;

        return $this;
    }


    public function getRentPriceStart()
    {
        return $this->rentPriceStart;
    }


    public function setRentPriceStart(?int $rentPriceStart)
    {
        $this->rentPriceStart = $rentPriceStart;

        return $this;
    }


    public function getRentPriceEnd()
    {
        return $this->rentPriceEnd;
    }


    public function setRentPriceEnd(?int $rentPriceEnd)
    {
        $this->rentPriceEnd = $rentPriceEnd;

        return $this;
    }


    public function getTypes()
    {
        return $this->types;
    }


    public function setTypes(array $types = null)
    {
        $this->types = $types;

        return $this;
    }


    public function getStartDateAfter()
    {
        return $this->startDateAfter;
    }


    public function setStartDateAfter(\DateTime $startDateAfter = null)
    {
        $this->startDateAfter = $startDateAfter;

        return $this;
    }


    public function getStartDateBefore()
    {
        return $this->startDateBefore;
    }


    public function setStartDateBefore(\DateTime $startDateBefore = null)
    {
        $this->startDateBefore = $startDateBefore;

        return $this;
    }


    public function getEndDateAfter()
    {
        return $this->endDateAfter;
    }


    public function setEndDateAfter(\DateTime $endDateAfter = null)
    {
        $this->endDateAfter = $endDateAfter;

        return $this;
    }


    public function getEndDateBefore()
    {
        return $this->endDateBefore;
    }


    public function setEndDateBefore(\DateTime $endDateBefore = null)
    {
        $this->endDateBefore = $endDateBefore;

        return $this;
    }


    public function withPictures()
    {
        return $this->withPictures;
    }


    public function setWithPictures(bool $withPictures)
    {
        $this->withPictures = $withPictures;

        return $this;
    }


    public function getEntityClass() : string
    {
        return AnnouncementPreference::class;
    }

}