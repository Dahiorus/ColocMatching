<?php

namespace App\Core\DTO\Announcement;

use App\Core\DTO\Invitation\InvitableDto;
use App\Core\DTO\Visit\VisitableDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\HousingType;
use App\Core\Service\VisitorInterface;
use App\Core\Validator\Constraint\DateRange;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Announcement
 *
 * @DateRange
 * @Serializer\ExclusionPolicy("ALL")
 *
 * @Hateoas\Relation(
 *   name="self",
 *   href= @Hateoas\Route(name="rest_get_announcement", absolute=true,
 *     parameters={ "id" = "expr(object.getId())" })
 * )
 * @Hateoas\Relation(
 *   name="pictures",
 *   embedded= @Hateoas\Embedded(content="expr(object.getPictures())")
 * )
 * @Hateoas\Relation(
 *   name="candidates",
 *   href= @Hateoas\Route(name="rest_get_announcement_candidates", absolute=true,
 *     parameters={ "id" = "expr(object.getId())" }),
 *   exclusion= @Hateoas\Exclusion(excludeIf="expr(not is_granted(['ROLE_USER']))")
 * )
 * @Hateoas\Relation(
 *   name="comments",
 *   href= @Hateoas\Route(name="rest_get_announcement_comments", absolute=true,
 *     parameters={ "id" = "expr(object.getId())" }),
 *   exclusion= @Hateoas\Exclusion(excludeIf="expr(not is_granted(['ROLE_USER']))")
 * )
 * @Hateoas\Relation(
 *   name="invitations",
 *   href= @Hateoas\Route(
 *     name="rest_get_announcement_invitations", absolute=true, parameters={ "id" = "expr(object.getId())" }),
 *   exclusion= @Hateoas\Exclusion(excludeIf="expr(not is_granted(['ROLE_USER']))")
 * )
 * @Hateoas\Relation(
 *   name="visits",
 *   href= @Hateoas\Route(
 *     name="rest_get_announcement_visits", absolute=true, parameters={ "id" = "expr(object.getId())" }),
 *   exclusion= @Hateoas\Exclusion(excludeIf="expr(not is_granted(['ROLE_USER']))")
 * )
 */
class AnnouncementDto extends AbstractAnnouncementDto implements VisitableDto, InvitableDto
{
    /**
     * Announcement description
     * @var string
     *
     * @Serializer\Expose
     * @SWG\Property(property="description", type="string")
     */
    private $description;

    /**
     * Announcement status
     * @var string
     *
     * @Assert\Choice(
     *   choices={ Announcement::STATUS_ENABLED, Announcement::STATUS_DISABLED, Announcement::STATUS_FILLED },
     *   strict=true)
     * @Serializer\Expose
     * @SWG\Property(property="status", type="string", default="enabled")
     */
    private $status;

    /**
     * Announcement location short representation
     * @var string
     *
     * @Serializer\Expose
     * @Serializer\SerializedName("shortLocation")
     * @SWG\Property(property="shortLocation", type="string", readOnly=true, example="Paris 75013")
     */
    private $shortLocation;

    /**
     * Housing type
     * @var string
     *
     * @Assert\Choice(choices={ HousingType::APARTMENT, HousingType::HOUSE, HousingType::STUDIO }, strict=true)
     * @Serializer\Expose
     * @SWG\Property(property="housingType", type="string", example="apartment")
     */
    private $housingType;

    /**
     * Number of rooms
     * @var integer
     *
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(1)
     * @Serializer\Expose
     * @Serializer\SerializedName("roomCount")
     * @SWG\Property(property="roomCount", type="number", default="1", example="3")
     */
    private $roomCount = 1;

    /**
     * Number of bedrooms
     * @var integer
     *
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @Serializer\Expose()
     * @Serializer\SerializedName("bedroomCount")
     * @SWG\Property(property="bedroomCount", type="number", default="0", example="2")
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
     * @SWG\Property(property="bathroomCount", type="number", default="0", example="1")
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
     * @SWG\Property(property="surfaceArea", type="number", default="0", example="40")
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
     * @SWG\Property(property="roomMateCount", type="number", default="0", example="1")
     */
    private $roomMateCount;

    /**
     * Announcement pictures
     * @var Collection<AnnouncementPictureDto>
     */
    private $pictures;


    public function __construct()
    {
        $this->pictures = new ArrayCollection();
    }


    public function __toString() : string
    {
        return parent::__toString() . "[description = " . $this->description . ", status = " . $this->status
            . ", shortLocation = " . $this->shortLocation . "]";
    }


    public function getDescription()
    {
        return $this->description;
    }


    public function setDescription(?string $description)
    {
        $this->description = $description;

        return $this;
    }


    public function getStatus()
    {
        return $this->status;
    }


    public function setStatus(?string $status)
    {
        $this->status = $status;

        return $this;
    }


    public function getShortLocation()
    {
        return $this->shortLocation;
    }


    public function setShortLocation(?string $shortLocation)
    {
        $this->shortLocation = $shortLocation;

        return $this;
    }


    public function getHousingType()
    {
        return $this->housingType;
    }


    public function setHousingType(?string $housingType)
    {
        $this->housingType = $housingType;

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


    public function getPictures() : Collection
    {
        return $this->pictures;
    }


    public function setPictures(Collection $pictures)
    {
        $this->pictures = $pictures;

        return $this;
    }


    public function accept(VisitorInterface $visitor)
    {
        $visitor->visit($this);
    }


    public function getEntityClass() : string
    {
        return Announcement::class;
    }
}