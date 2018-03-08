<?php

namespace ColocMatching\CoreBundle\DTO\Announcement;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\Entity\Announcement\Housing;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Housing")
 *
 * @author Dahiorus
 */
class HousingDto extends AbstractDto
{
    /**
     * Housing type
     * @var string
     * @Assert\Choice(choices={ Housing::TYPE_APARTMENT, Housing::TYPE_HOUSE, Housing::TYPE_STUDIO }, strict=true)
     * @Serializer\Expose
     * @SWG\Property(enum={ "apartment", "house", "studio" })
     */
    private $type;

    /**
     * Number of rooms
     * @var integer
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @Serializer\Expose
     * @Serializer\SerializedName("roomCount")
     * @SWG\Property(default=0)
     */
    private $roomCount;

    /**
     * Number of bedrooms
     * @var integer
     *
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @Serializer\Expose()
     * @Serializer\SerializedName("bedroomCount")
     * @SWG\Property(default=0)
     */
    private $bedroomCount;

    /**
     * Number of bathrooms
     * @var integer
     *
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @Serializer\Expose
     * @Serializer\SerializedName("bathroomCount")
     * @SWG\Property(default=0)
     */
    private $bathroomCount;

    /**
     * Surface area (m²)
     * @var integer
     *
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @Serializer\Expose
     * @Serializer\SerializedName("surfaceArea")
     * @SWG\Property(default=0)
     */
    private $surfaceArea;

    /**
     * Number of roommates
     * @var integer
     *
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @Serializer\Expose
     * @Serializer\SerializedName("roomMateCount")
     * @SWG\Property(default=0)
     */
    private $roomMateCount;


    public function __toString() : string
    {
        return parent::__toString() . "[type =" . $this->type . ", roomCount = " . $this->roomCount
            . ", bedroomCount = " . $this->bedroomCount . ", bathroomCount = " . $this->bathroomCount
            . ", surfaceArea = " . $this->surfaceArea . ", roomMateCount = " . $this->roomMateCount . "]";
    }


    public function getType()
    {
        return $this->type;
    }


    public function setType(?string $type) : HousingDto
    {
        $this->type = $type;

        return $this;
    }


    public function getRoomCount()
    {
        return $this->roomCount;
    }


    public function setRoomCount(?int $roomCount) : HousingDto
    {
        $this->roomCount = $roomCount;

        return $this;
    }


    public function getBedroomCount()
    {
        return $this->bedroomCount;
    }


    public function setBedroomCount(?int $bedroomCount) : HousingDto
    {
        $this->bedroomCount = $bedroomCount;

        return $this;
    }


    public function getBathroomCount()
    {
        return $this->bathroomCount;
    }


    public function setBathroomCount(?int $bathroomCount) : HousingDto
    {
        $this->bathroomCount = $bathroomCount;

        return $this;
    }


    public function getSurfaceArea()
    {
        return $this->surfaceArea;
    }


    public function setSurfaceArea(?int $surfaceArea) : HousingDto
    {
        $this->surfaceArea = $surfaceArea;

        return $this;
    }


    public function getRoomMateCount()
    {
        return $this->roomMateCount;
    }


    public function setRoomMateCount(?int $roomMateCount) : HousingDto
    {
        $this->roomMateCount = $roomMateCount;

        return $this;
    }


    public function getEntityClass() : string
    {
        return Housing::class;
    }
}