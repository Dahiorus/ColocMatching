<?php

namespace App\Core\DTO\User;

use App\Core\DTO\AbstractDto;
use App\Core\Entity\Announcement\AnnouncementType;
use App\Core\Entity\User\AnnouncementPreference;
use App\Core\Validator\Constraint\AddressValue;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("ALL")
 *
 * @author Dahiorus
 */
class AnnouncementPreferenceDto extends AbstractDto
{
    /**
     * Search area location filter
     * @var string
     *
     * @AddressValue
     * @Serializer\Expose
     * @SWG\Property(property="address", type="string", example="Paris 75001")
     */
    private $address;

    /**
     * Rent price start range filter
     * @var integer
     *
     * @Assert\GreaterThanOrEqual(0)
     * @Serializer\SerializedName("rentPriceStart")
     * @Serializer\Expose
     * @SWG\Property(property="rentPriceStart", type="integer", example="300")
     */
    private $rentPriceStart;

    /**
     * Rent price end range filter
     * @var integer
     *
     * @Assert\GreaterThanOrEqual(0)
     * @Serializer\SerializedName("rentPriceEnd")
     * @Serializer\Expose
     * @SWG\Property(property="rentPriceEnd", type="integer", example="1500")
     */
    private $rentPriceEnd;

    /**
     * Announcement types filter
     * @var array
     *
     * @Assert\Choice(
     *   choices={ AnnouncementType::RENT, AnnouncementType::SUBLEASE, AnnouncementType::SHARING },
     *   multiple=true, strict=true)
     * @Serializer\Expose
     * @SWG\Property(property="types", type="array", uniqueItems=true, @SWG\Items(type="string"))
     */
    private $types = array ();

    /**
     * Start date 'from' filter
     * @var \DateTime
     *
     * @Assert\Date
     * @Serializer\SerializedName("startDateAfter")
     * @Serializer\Expose
     * @SWG\Property(property="startDateAfter", type="string", format="date")
     */
    private $startDateAfter;

    /**
     * Start date 'to' filter
     * @var \DateTime
     *
     * @Assert\Date
     * @Serializer\SerializedName("startDateBefore")
     * @Serializer\Expose
     * @SWG\Property(property="startDateBefore", type="string", format="date")
     */
    private $startDateBefore;

    /**
     * End date 'from' filter
     * @var \DateTime
     *
     * @Assert\Date
     * @Serializer\SerializedName("endDateAfter")
     * @Serializer\Expose
     * @SWG\Property(property="endDateAfter", type="string", format="date")
     */
    private $endDateAfter;

    /**
     * End date 'to' filter
     * @var \DateTime
     *
     * @Assert\Date
     * @Serializer\SerializedName("endDateBefore")
     * @Serializer\Expose
     * @SWG\Property(property="endDateBefore", type="string", format="date")
     */
    private $endDateBefore;

    /**
     * Only announcements with pictures
     * @var boolean
     *
     * @Serializer\SerializedName("withPictures")
     * @Serializer\Expose
     * @SWG\Property(property="withPictures", type="boolean")
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