<?php

namespace ColocMatching\CoreBundle\DTO\Group;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\DTO\Invitation\InvitableDto;
use ColocMatching\CoreBundle\DTO\VisitableDto;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Service\VisitorInterface;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Group", required={ "name", "status" },
 *   allOf={ @SWG\Schema(ref="#/definitions/AbstractDto") })
 * @Hateoas\Relation(
 *   name="self",
 *   href= @Hateoas\Route(name="rest_get_group", absolute=true,
 *     parameters={ "id" = "expr(object.getId())" })
 * )
 * @Hateoas\Relation(
 *   name="creator",
 *   href= @Hateoas\Route(
 *     name="rest_get_user", absolute=true, parameters={ "id" = "expr(object.getCreatorId())" })
 * )
 * @Hateoas\Relation(
 *   name="picture",
 *   embedded= @Hateoas\Embedded(content="expr(object.getPicture())")
 * )
 * @Hateoas\Relation(
 *   name="members",
 *   href= @Hateoas\Route(
 *     name="rest_get_group_members", absolute=true, parameters={ "id" = "expr(object.getId())" })
 * )
 * @Hateoas\Relation(
 *   name="invitations",
 *   href= @Hateoas\Route(
 *     name="rest_get_group_invitations", absolute=true, parameters={ "id" = "expr(object.getId())" })
 * )
 * @Hateoas\Relation(
 *   name="visits",
 *   href= @Hateoas\Route(
 *     name="rest_get_group_visits", absolute=true, parameters={ "id" = "expr(object.getId())" })
 * )
 */
class GroupDto extends AbstractDto implements VisitableDto, InvitableDto
{
    /**
     * Group name
     * @var string
     * @Assert\NotBlank
     * @Serializer\Expose
     * @SWG\Property
     */
    private $name;

    /**
     * Group description
     * @var string
     * @Serializer\Expose
     * @SWG\Property
     */
    private $description;

    /**
     * Group budget
     * @var integer
     * @Assert\GreaterThanOrEqual(0)
     * @Serializer\Expose
     * @SWG\Property
     */
    private $budget;

    /**
     * Group status
     * @var string
     * @Assert\Choice(choices={ Group::STATUS_CLOSED, Group::STATUS_OPENED }, strict=true)
     * @Serializer\Expose
     * @SWG\Property(enum={ "closed", "opened" }, default="opened")
     */
    private $status;

    /**
     * @var integer
     */
    private $creatorId;

    /**
     * Group picture
     * @var GroupPictureDto
     */
    private $picture;


    public function __toString() : string
    {
        return parent::__toString() . "[name =" . $this->name . ", description = " . $this->description
            . ", budget = " . $this->budget . ", status = " . $this->status . ", creatorId = " . $this->creatorId . "]";
    }


    public function getName()
    {
        return $this->name;
    }


    public function setName(?string $name) : GroupDto
    {
        $this->name = $name;

        return $this;
    }


    public function getDescription()
    {
        return $this->description;
    }


    public function setDescription(?string $description) : GroupDto
    {
        $this->description = $description;

        return $this;
    }


    public function getBudget()
    {
        return $this->budget;
    }


    public function setBudget(?int $budget) : GroupDto
    {
        $this->budget = $budget;

        return $this;
    }


    public function getStatus()
    {
        return $this->status;
    }


    public function setStatus(?string $status) : GroupDto
    {
        $this->status = $status;

        return $this;
    }


    public function getCreatorId()
    {
        return $this->creatorId;
    }


    public function setCreatorId(?int $creatorId) : GroupDto
    {
        $this->creatorId = $creatorId;

        return $this;
    }


    public function getPicture()
    {
        return $this->picture;
    }


    public function setPicture(GroupPictureDto $picture = null) : GroupDto
    {
        $this->picture = $picture;

        return $this;
    }


    public function accept(VisitorInterface $visitor)
    {
        $visitor->visit($this);
    }


    public function getEntityClass() : string
    {
        return Group::class;
    }

}