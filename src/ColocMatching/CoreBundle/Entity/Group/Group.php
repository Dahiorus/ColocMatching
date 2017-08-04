<?php

namespace ColocMatching\CoreBundle\Entity\Group;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Entity\Updatable;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Group
 *
 * @ORM\Table(
 *   name="app_group",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UK_GROUP_CREATOR", columns={"creator_id"}),
 *     @ORM\UniqueConstraint(name="UK_GROUP_PICTURE", columns={"picture_id"})
 * })
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\Group\GroupRepository")
 * @ORM\EntityListeners({"ColocMatching\CoreBundle\Listener\UpdatableListener"})
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Group")
 */
class Group implements EntityInterface, Updatable, Visitable {

    const STATUS_CLOSED = "closed";

    const STATUS_OPENED = "opened";

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     * @SWG\Property(description="Group ID", readOnly=true)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="group_name", length=255, nullable=false)
     * @Assert\NotBlank()
     * @JMS\Expose()
     * @SWG\Property(description="Group name")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @JMS\Expose()
     * @SWG\Property(description="Group description")
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="budget", type="integer", options={ "default": 0 })
     * @JMS\Expose()
     * @SWG\Property(description="Group budget")
     */
    private $budget;

    /**
     * @var string
     *
     * @ORM\Column(name="status", nullable=false, options={ "default": Group::STATUS_OPENED })
     * @Assert\Choice(choices={ Group::STATUS_CLOSED, Group::STATUS_OPENED }, strict=true)
     * @JMS\Expose()
     * @SWG\Property(description="Group status", enum={ "closed", "opened" }, default="opened")
     */
    private $status = self::STATUS_OPENED;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User",
     *   inversedBy="group", fetch="LAZY")
     * @ORM\JoinColumn(name="creator_id", nullable=false)
     * @Assert\NotNull()
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
     * @Assert\Valid()
     */
    private $picture;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_update", type="datetime")
     */
    private $lastUpdate;


    public function __construct(User $creator) {
        $this->creator = $creator;
        $this->members = new ArrayCollection();
        $this->addMember($creator);
    }


    public function addMember(User $member = null) {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }


    public function __toString() {
        $createdAt = empty($this->createdAt) ? null : $this->createdAt->format(\DateTime::ISO8601);
        $lastUpdate = empty($this->lastUpdate) ? null : $this->lastUpdate->format(\DateTime::ISO8601);

        return "Group [id=" . $this->id . ", name='" . $this->name . "', description='" . $this->description .
            "', budget=" . $this->budget . ", status='" . $this->status . "', creator=" . $this->creator .
            ", createdAt=" . $createdAt . ", lastUpdate=" . $lastUpdate . "]";
    }


    public function getId() : int {
        return $this->id;
    }


    public function setId(int $id) {
        $this->id = $id;

        return $this;
    }


    public function getName() {
        return $this->name;
    }


    public function setName(?string $name) {
        $this->name = $name;

        return $this;
    }


    public function getDescription() {
        return $this->description;
    }


    public function setDescription(?string $description) {
        $this->description = $description;

        return $this;
    }


    public function getBudget() {
        return $this->budget;
    }


    public function setBudget(int $budget) {
        $this->budget = $budget;

        return $this;
    }


    public function getStatus() {
        return $this->status;
    }


    public function setStatus(?string $status) {
        $this->status = $status;

        return $this;
    }


    /**
     * Get creator
     *
     * @return \ColocMatching\CoreBundle\Entity\User\User
     */
    public function getCreator() {
        return $this->creator;
    }


    /**
     * Set creator
     *
     * @param User $creator
     *
     * @return Group
     */
    public function setCreator(User $creator) {
        $this->creator = $creator;

        return $this;
    }


    /**
     * Get members
     *
     * @return Collection
     */
    public function getMembers() {
        return $this->members;
    }


    public function setMembers(Collection $members = null) {
        $this->members = $members;

        return $this;
    }


    public function removeMember(User $user = null) {
        $this->members->removeElement($user);
    }


    public function hasMembers() {
        return !$this->members->isEmpty();
    }


    public function getPicture() {
        return $this->picture;
    }


    public function setPicture(GroupPicture $picture = null) {
        $this->picture = $picture;

        return $this;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Entity\Updatable::getCreatedAt()
     */
    public function getCreatedAt() : \DateTime {
        return $this->createdAt;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Entity\Updatable::setCreatedAt()
     */
    public function setCreatedAt(\DateTime $createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Entity\Updatable::getLastUpdate()
     */
    public function getLastUpdate() : \DateTime {
        return $this->lastUpdate;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Entity\Updatable::setLastUpdate()
     */
    public function setLastUpdate(\DateTime $lastUpdate) {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

}