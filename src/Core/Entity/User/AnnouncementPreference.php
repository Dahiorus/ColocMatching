<?php

namespace App\Core\Entity\User;

use App\Core\Entity\AbstractEntity;
use App\Core\Entity\Announcement\Address;
use Doctrine\ORM\Mapping as ORM;

/**
 * AnnouncementPreference
 *
 * @ORM\Table(name="announcement_preference")
 * @ORM\Entity
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="announcement_preferences")
 */
class AnnouncementPreference extends AbstractEntity
{
    /**
     * @var Address
     * @ORM\Embedded(class = "App\Core\Entity\Announcement\Address")
     */
    private $address;

    /**
     * @var integer
     *
     * @ORM\Column(name="rent_price_start", type="integer", nullable=true)
     */
    private $rentPriceStart;

    /**
     * @var integer
     *
     * @ORM\Column(name="rent_price_end", type="integer", nullable=true)
     */
    private $rentPriceEnd;

    /**
     * @var array
     *
     * @ORM\Column(name="types", type="simple_array", nullable=true)
     */
    private $types = [];

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date_after", type="date", nullable=true)
     */
    private $startDateAfter;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date_before", type="date", nullable=true)
     */
    private $startDateBefore;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date_after", type="date", nullable=true)
     */
    private $endDateAfter;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date_before", type="date", nullable=true)
     */
    private $endDateBefore;

    /**
     * @var boolean
     *
     * @ORM\Column(name="with_pictures", type="boolean", options={"default": false})
     */
    private $withPictures = false;


    public function __toString() : string
    {
        $startDateAfter = empty($this->startDateAfter) ? "" : $this->startDateAfter->format(\DateTime::ISO8601);
        $startDateBefore = empty($this->startDateBefore) ? "" : $this->startDateBefore->format(\DateTime::ISO8601);
        $endDateAfter = empty($this->endDateAfter) ? "" : $this->endDateAfter->format(\DateTime::ISO8601);
        $endDateBefore = empty($this->endDateBefore) ? "" : $this->endDateBefore->format(\DateTime::ISO8601);

        return parent::__toString() . "[location = " . $this->address . ", rentPriceStart = " . $this->rentPriceStart
            . ", rentPriceEnd = " . $this->rentPriceEnd . ", types = " . implode(", ", $this->types)
            . ", startDateAfter = " . $startDateAfter . ", startDateBefore = " . $startDateBefore
            . ", endDateAfter = " . $endDateAfter . ", endDateBefore = " . $endDateBefore
            . ", withPictures = " . $this->withPictures . "]";
    }


    public function getAddress()
    {
        return $this->address;
    }


    public function setAddress(Address $address = null)
    {
        $this->address = $address;

        return $this;
    }


    public function getRentPriceStart()
    {
        return $this->rentPriceStart;
    }


    public function setRentPriceStart(int $rentPriceStart = null)
    {
        $this->rentPriceStart = $rentPriceStart;

        return $this;
    }


    public function getRentPriceEnd()
    {
        return $this->rentPriceEnd;
    }


    public function setRentPriceEnd(int $rentPriceEnd = null)
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

}
