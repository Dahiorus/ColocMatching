<?php

namespace App\Core\Entity\Announcement;

use App\Core\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Housing
 *
 * @ORM\Table(name="housing")
 * @ORM\Entity
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="housings")
 *
 * @author Dahiorus
 */
class Housing extends AbstractEntity
{
    const TYPE_APARTMENT = "apartment";

    const TYPE_HOUSE = "house";

    const TYPE_STUDIO = "studio";

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=true)
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="room_count", type="integer", nullable=true)
     */
    private $roomCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="bedroom_count", type="integer", nullable=true)
     */
    private $bedroomCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="bathroom_count", type="integer", nullable=true)
     */
    private $bathroomCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="surface_area", type="integer", nullable=true)
     */
    private $surfaceArea = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="roommate_count", type="integer", nullable=true)
     */
    private $roomMateCount = 0;


    public function __toString()
    {
        return parent::__toString() . "[type = " . $this->type . ", roomCount = " . $this->roomCount
            . ", bedroomCount = " . $this->bedroomCount . ", bathroomCount = " . $this->bathroomCount
            . ", surfaceArea = " . $this->surfaceArea . ", roomMateCount = " . $this->roomMateCount . "]";
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


    public function getRoomCount()
    {
        return $this->roomCount;
    }


    public function setRoomCount(?int $roomCount)
    {
        $this->roomCount = $roomCount;

        return $this;
    }


    public function getBedroomCount()
    {
        return $this->bedroomCount;
    }


    public function setBedroomCount(?int $bedroomCount)
    {
        $this->bedroomCount = $bedroomCount;

        return $this;
    }


    public function getBathroomCount()
    {
        return $this->bathroomCount;
    }


    public function setBathroomCount(?int $bathroomCount)
    {
        $this->bathroomCount = $bathroomCount;

        return $this;
    }


    public function getSurfaceArea()
    {
        return $this->surfaceArea;
    }


    public function setSurfaceArea(?int $surfaceArea)
    {
        $this->surfaceArea = $surfaceArea;

        return $this;
    }


    public function getRoomMateCount()
    {
        return $this->roomMateCount;
    }


    public function setRoomMateCount(?int $roomMateCount)
    {
        $this->roomMateCount = $roomMateCount;

        return $this;
    }

}