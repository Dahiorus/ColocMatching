<?php

namespace ColocMatching\CoreBundle\Entity\Group;

use ColocMatching\CoreBundle\Entity\AbstractEntity;
use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Group
 *
 * @ORM\Table(
 *   name="app_group",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UK_GROUP_CREATOR", columns={"creator_id"}),
 *     @ORM\UniqueConstraint(name="UK_GROUP_PICTURE", columns={"picture_id"})
 * }, indexes={
 *   @ORM\Index(name="IDX_GROUP_STATUS", columns={ "status" }),
 *   @ORM\Index(name="IDX_GROUP_BUDGET", columns={ "budget" })
 * })
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\Group\GroupRepository")
 *
 * @author Dahiorus
 */
class Group extends AbstractEntity implements Visitable, Invitable
{
    const STATUS_CLOSED = "closed";

    const STATUS_OPENED = "opened";

    /**
     * @var string
     *
     * @ORM\Column(name="group_name", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="budget", type="integer", nullable=true)
     */
    private $budget;

    /**
     * @var string
     *
     * @ORM\Column(name="status", nullable=false, options={ "default": Group::STATUS_OPENED })
     */
    private $status = self::STATUS_OPENED;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User",
     *   inversedBy="group", fetch="LAZY")
     * @ORM\JoinColumn(name="creator_id", nullable=false)
     */
    private $creator;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="group_member",
     *   joinColumns={
     *     @ORM\JoinColumn(name="group_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="user_id", unique=true)
     * })
     */
    private $members;

    /**
     * @var GroupPicture
     *
     * @ORM\OneToOne(targetEntity="GroupPicture", cascade={ "persist", "remove" }, fetch="LAZY")
     * @ORM\JoinColumn(name="picture_id", nullable=true, onDelete="SET NULL")
     */
    private $picture;


    public function __construct(User $creator)
    {
        $this->creator = $creator;
        $this->members = new ArrayCollection();
        $this->addMember($creator);
    }


    public function __toString()
    {
        return parent::__toString() . "[name =" . $this->name . ", description = " . $this->description
            . ", budget = " . $this->budget . ", status = " . $this->status . "]";
    }


    public function getName()
    {
        return $this->name;
    }


    public function setName(?string $name)
    {
        $this->name = $name;

        return $this;
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


    public function getBudget()
    {
        return $this->budget;
    }


    public function setBudget(?int $budget)
    {
        $this->budget = $budget;

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


    public function isOpened()
    {
        return $this->status == self::STATUS_OPENED;
    }


    /**
     * Get creator
     *
     * @return \ColocMatching\CoreBundle\Entity\User\User
     */
    public function getCreator() : User
    {
        return $this->creator;
    }


    /**
     * Set creator
     *
     * @param User $creator
     *
     * @return Group
     */
    public function setCreator(User $creator)
    {
        $this->creator = $creator;

        return $this;
    }


    /**
     * Get members
     *
     * @return Collection
     */
    public function getMembers()
    {
        return $this->members;
    }


    public function setMembers(Collection $members = null)
    {
        $this->members = $members;

        return $this;
    }


    public function addMember(User $member = null)
    {
        if (!$this->members->contains($member))
        {
            $this->members->add($member);
        }

        return $this;
    }


    public function removeMember(User $user = null)
    {
        if (empty($user))
        {
            return;
        }

        $memberToDelete = $this->members->filter(function (User $m) use ($user) {
            return $m->getId() == $user->getId();
        })->first();

        $this->members->removeElement($memberToDelete);
    }


    public function hasMembers()
    {
        return !$this->members->isEmpty();
    }


    public function getPicture()
    {
        return $this->picture;
    }


    public function setPicture(GroupPicture $picture = null)
    {
        $this->picture = $picture;

        return $this;
    }


    public function getInvitees() : Collection
    {
        return $this->getMembers();
    }


    public function setInvitees(Collection $invitees = null)
    {
        return $this->setMembers($invitees);
    }


    public function addInvitee(User $invitee = null)
    {
        return $this->addMember($invitee);
    }


    public function removeInvitee(User $invitee = null)
    {
        $this->removeMember($invitee);
    }


    public function hasInvitee(User $invitee) : bool
    {
        return $this->members->contains($invitee);
    }


    public function isAvailable() : bool
    {
        return $this->isOpened();
    }

}