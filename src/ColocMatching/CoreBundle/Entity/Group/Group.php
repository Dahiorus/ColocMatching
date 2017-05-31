<?php

namespace ColocMatching\CoreBundle\Entity\Group;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;
use ColocMatching\CoreBundle\Entity\Updatable;

/**
 * Group
 *
 * @ORM\Table(
 *   name="app_group",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="app_group_user_unique", columns={"user_id"})
 * })
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\Group\GroupRepository")
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Group")
 */
class Group implements EntityInterface, Updatable {

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
     * @var User
     *
     * @ORM\OneToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User",
     *   inversedBy="group", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull()
     */
    private $creator;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="group_member",
     *   joinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="group_id", referencedColumnName="id", unique=true)
     * })
     */
    private $members;

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
        $this->members->add($creator);
    }


    public function getId() {
        return $this->id;
    }


    public function getName() {
        return $this->name;
    }


    public function setName(string $name) {
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
     * @return \ColocMatching\CoreBundle\Entity\Announcement\Announcement
     */
    public function setCreator(User $creator) {
        $this->creator = $creator;
        return $this;
    }


    /**
     * Get members
     *
     * @return ArrayCollection
     */
    public function getMembers() {
        return $this->members;
    }


    public function setMembers(ArrayCollection $members = null) {
        $this->members = $members;
        return $this;
    }


    public function addMember(User $member = null) {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }


    public function removeMember(User $user = null) {
        $this->members->removeElement($user);
    }


    public function hasMembers() {
        return !$this->members->isEmpty();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Entity\Updatable::getCreatedAt()
     */
    public function getCreatedAt(): \DateTime {
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
    public function getLastUpdate(): \DateTime {
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