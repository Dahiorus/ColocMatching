<?php

namespace App\Core\Repository\Filter;

use Swagger\Annotations as SWG;

/**
 * Housing query filter class
 *
 * @SWG\Definition(definition="HousingFilter")
 *
 * @author Dahiorus
 */
class HousingFilter
{
    /**
     * @var array
     */
    private $types = array ();

    /**
     * @var integer
     */
    private $roomCount;

    /**
     * @var integer
     */
    private $bedroomCount;

    /**
     * @var integer
     */
    private $bathroomCount;

    /**
     * @var integer
     */
    private $surfaceAreaMin;

    /**
     * @var integer
     */
    private $surfaceAreaMax;

    /**
     * @var integer
     */
    private $roomMateCount;


    public function __toString()
    {
        return sprintf(
            "HousingFilter [types: '%s', roomCount: %d, bedroomCount: %d, bathroomCount: %d, surfaceArea: [%d - %d], roomMateCount: %d]",
            implode(",", $this->types), $this->roomCount, $this->bedroomCount, $this->bathroomCount,
            $this->surfaceAreaMin, $this->surfaceAreaMax, $this->roomMateCount);
    }


    public function getTypes()
    {
        return $this->types;
    }


    public function setTypes(?array $types)
    {
        $this->types = $types;

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


    public function getSurfaceAreaMin()
    {
        return $this->surfaceAreaMin;
    }


    public function setSurfaceAreaMin(?int $surfaceAreaMin)
    {
        $this->surfaceAreaMin = $surfaceAreaMin;

        return $this;
    }


    public function getSurfaceAreaMax()
    {
        return $this->surfaceAreaMax;
    }


    public function setSurfaceAreaMax(?int $surfaceAreaMax)
    {
        $this->surfaceAreaMax = $surfaceAreaMax;

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