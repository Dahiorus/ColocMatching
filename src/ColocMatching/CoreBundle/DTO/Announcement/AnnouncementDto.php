<?php

namespace ColocMatching\CoreBundle\DTO\Announcement;

use ColocMatching\CoreBundle\DTO\Invitation\InvitableDto;
use ColocMatching\CoreBundle\DTO\VisitableDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Service\VisitorInterface;
use ColocMatching\CoreBundle\Validator\Constraint\DateRange;
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
 * @SWG\Definition(definition="Announcement", allOf={ @SWG\Schema(ref="#/definitions/AbstractAnnouncement") })
 * @Hateoas\Relation(
 *   name="self",
 *   href= @Hateoas\Route(name="rest_get_announcement", absolute=true,
 *     parameters={ "id" = "expr(object.getId())" })
 * )
 * @Hateoas\Relation(
 *   name="housing",
 *   href= @Hateoas\Route(name="rest_get_announcement_housing", absolute=true,
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
     * @Serializer\Expose
     * @SWG\Property
     */
    private $description;

    /**
     * Announcement status
     * @var string
     * @Assert\Choice(
     *   choices={ Announcement::STATUS_ENABLED, Announcement::STATUS_DISABLED, Announcement::STATUS_FILLED },
     *   strict=true)
     * @Serializer\Expose
     * @SWG\Property(enum={ "enabled", "disabled", "filled" }, default="enabled")
     */
    private $status;

    /**
     * Announcement location short representation
     * @var string
     * @Serializer\Expose
     * @Serializer\SerializedName("shortLocation")
     * @SWG\Property(readOnly=true)
     */
    private $shortLocation;

    /**
     * Announcement pictures
     * @var Collection<AnnouncementPictureDto>
     */
    private $pictures;


    public function __construct()
    {
        $this->pictures = new ArrayCollection();
    }


    /**
     * @var integer
     */
    private $housingId;


    public function __toString() : string
    {
        return parent::__toString() . "[description = " . $this->description . ", status = " . $this->status
            . ", shortLocation = " . $this->shortLocation . "]";
    }


    public function getDescription()
    {
        return $this->description;
    }


    public function setDescription(?string $description) : AnnouncementDto
    {
        $this->description = $description;

        return $this;
    }


    public function getStatus()
    {
        return $this->status;
    }


    public function setStatus(?string $status) : AnnouncementDto
    {
        $this->status = $status;

        return $this;
    }


    public function getShortLocation()
    {
        return $this->shortLocation;
    }


    public function setShortLocation(?string $shortLocation) : AnnouncementDto
    {
        $this->shortLocation = $shortLocation;

        return $this;
    }


    public function getHousingId()
    {
        return $this->housingId;
    }


    public function setHousingId(?int $housingId)
    {
        $this->housingId = $housingId;

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