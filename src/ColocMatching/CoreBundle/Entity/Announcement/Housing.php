<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Housing
 *
 * @ORM\Table(name="housing")
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Housing")
 *
 * @author Dahiorus
 */
class Housing implements EntityInterface {

    const TYPE_APARTMENT = "apartment";

    const TYPE_HOUSE = "house";

    const TYPE_STUDIO = "studio";

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     * @SWG\Property(description="Housing id", readOnly=true)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=true)
     * @Assert\Choice(choices={"apartment", "house", "studio"}, strict=true)
     * @JMS\Expose()
     * @SWG\Property(description="Housing type")
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="room_count", type="integer", nullable=true)
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @JMS\Expose()
     * @SWG\Property(description="Number of rooms")
     */
    private $roomCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="bedroom_count", type="integer", nullable=true)
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @JMS\Expose()
     * @SWG\Property(description="Number of bedrooms")
     */
    private $bedroomCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="bathroom_count", type="integer", nullable=true)
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @JMS\Expose()
     * @SWG\Property(description="Number of bathrooms")
     */
    private $bathroomCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="surface_area", type="integer", nullable=true)
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @JMS\Expose()
     * @SWG\Property(description="Surface area (m²)")
     */
    private $surfaceArea = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="roommate_count", type="integer", nullable=true)
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @JMS\Expose()
     * @SWG\Property(description="Number of roommates")
     */
    private $roomMateCount = 0;


    public function __toString() {
        return sprintf(
            "Housing [id: %d, type: '%s', roomCount: %d, bedroomCount %d, bathroomCount: %d, surfaceArea: %d, roomMateCount: %d]",
            $this->id, $this->roomCount, $this->bedroomCount, $this->bathroomCount, $this->surfaceArea,
            $this->roomMateCount);
    }


    public function getId() {
        return $this->id;
    }


    public function getType() {
        return $this->type;
    }


    public function setType($type) {
        $this->type = $type;
        return $this;
    }


    public function getRoomCount() {
        return $this->roomCount;
    }


    public function setRoomCount(int $roomCount = null) {
        $this->roomCount = $roomCount;
        return $this;
    }


    public function getBedroomCount() {
        return $this->bedroomCount;
    }


    public function setBedroomCount(int $bedroomCount = null) {
        $this->bedroomCount = $bedroomCount;
        return $this;
    }


    public function getBathroomCount() {
        return $this->bathroomCount;
    }


    public function setBathroomCount(int $bathroomCount = null) {
        $this->bathroomCount = $bathroomCount;
        return $this;
    }


    public function getSurfaceArea() {
        return $this->surfaceArea;
    }


    public function setSurfaceArea(int $surfaceArea = null) {
        $this->surfaceArea = $surfaceArea;
        return $this;
    }


    public function getRoomMateCount() {
        return $this->roomMateCount;
    }


    public function setRoomMateCount(int $roomMateCount = null) {
        $this->roomMateCount = $roomMateCount;
        return $this;
    }

}