<?php

namespace App\Core\Entity\User;

use App\Core\Entity\AbstractEntity;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Group\Group;
use App\Core\Entity\Tag\Taggable;
use App\Core\Entity\Visit\Visitable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(
 *   name="app_user",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UK_USER_EMAIL", columns={"email"}),
 *     @ORM\UniqueConstraint(name="UK_USER_ANNOUNCEMENT", columns={"announcement_id"}),
 *     @ORM\UniqueConstraint(name="UK_USER_GROUP", columns={"group_id"}),
 *     @ORM\UniqueConstraint(name="UK_UNIQUE_PICTURE", columns={"picture_id"}),
 *     @ORM\UniqueConstraint(name="UK_USER_ANNOUNCEMENT_PREFERENCE", columns={"announcement_preference_id"}),
 *     @ORM\UniqueConstraint(name="UK_USER_PROFILE_PREFERENCE", columns={"user_preference_id"})
 * }, indexes={
 *     @ORM\Index(name="IDX_USER_EMAIL", columns={ "email" }),
 *     @ORM\Index(name="IDX_USER_STATUS", columns={ "status" }),
 *     @ORM\Index(name="IDX_USER_TYPE", columns={ "type" })
 * })
 * @ORM\Entity(repositoryClass="App\Core\Repository\User\UserRepository")
 * @ORM\EntityListeners({
 *   "App\Core\Listener\CacheDriverListener",
 *   "App\Core\Listener\UpdateListener",
 *   "App\Core\Listener\UserListener"
 * })
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="users")
 *
 * @author Dahiorus
 */
class User extends AbstractEntity implements UserInterface, Visitable, Taggable
{
    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=64, nullable=false)
     */
    private $password;

    /**
     * @var string
     */
    private $plainPassword;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", options={"default": "pending"})
     */
    private $status = UserStatus::PENDING;

    /**
     * User roles
     * @var array
     * @ORM\Column(name="roles", type="simple_array")
     */
    private $roles = [];

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=255)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255)
     */
    private $lastName;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=15, nullable=true)
     */
    private $type;

    /**
     * @var string
     * @ORM\Column(name="gender", type="string", nullable=true)
     */
    private $gender;

    /**
     * @var \DateTime
     * @ORM\Column(name="birth_date", type="date", nullable=true)
     */
    private $birthDate;

    /**
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(name="phone_number", type="string", length=10, nullable=true)
     */
    private $phoneNumber;

    /**
     * @var Collection<Tag>
     *
     * @ORM\ManyToMany(targetEntity="App\Core\Entity\Tag\Tag", fetch="EXTRA_LAZY", cascade={ "persist", "merge" })
     * @ORM\JoinTable(name="user_tag",
     *   joinColumns={
     *     @ORM\JoinColumn(name="user_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="tag_id")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="announcement_candidates")
     */
    private $tags;

    /**
     * @var Announcement
     * @ORM\OneToOne(targetEntity="App\Core\Entity\Announcement\Announcement",
     *   cascade={"remove"}, mappedBy="creator", fetch="LAZY")
     * @ORM\JoinColumn(name="announcement_id")
     */
    private $announcement;

    /**
     * @var Group
     * @ORM\OneToOne(targetEntity="App\Core\Entity\Group\Group",
     *   cascade={"remove"}, mappedBy="creator", fetch="LAZY")
     * @ORM\JoinColumn(name="group_id", onDelete="SET NULL")
     */
    private $group;

    /**
     * @var ProfilePicture
     * @ORM\OneToOne(targetEntity="App\Core\Entity\User\ProfilePicture",
     *   cascade={"persist", "merge", "remove"}, fetch="LAZY")
     * @ORM\JoinColumn(name="picture_id", onDelete="SET NULL")
     */
    private $picture;

    /**
     * @var AnnouncementPreference
     * @ORM\OneToOne(targetEntity=AnnouncementPreference::class, cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\JoinColumn(name="announcement_preference_id")
     */
    private $announcementPreference;

    /**
     * @var UserPreference
     * @ORM\OneToOne(targetEntity=UserPreference::class, cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\JoinColumn(name="user_preference_id")
     */
    private $userPreference;

    /**
     * @var \DateTime
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    private $lastLogin;


    /**
     * User constructor.
     *
     * @param string $email
     * @param string|null $plainPassword
     * @param string $firstName
     * @param string $lastName
     */
    public function __construct(string $email, ?string $plainPassword, string $firstName, string $lastName)
    {
        $this->email = $email;
        $this->plainPassword = $plainPassword;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->setRoles(array (UserRole::ROLE_DEFAULT));
        $this->announcementPreference = new AnnouncementPreference();
        $this->userPreference = new UserPreference();
        $this->tags = new ArrayCollection();
    }


    public function __toString()
    {
        $lastLogin = empty($this->lastLogin) ? null : $this->lastLogin->format(\DateTime::ISO8601);

        return parent::__toString() . "[email='" . $this->email . "', status='" . $this->status
            . "', roles={" . implode(",", $this->getRoles()) . "}, firstName='" . $this->firstName
            . "', lastName='" . $this->lastName . "', type='" . $this->type . ", lastLogin=" . $lastLogin . "]";
    }


    public function getRoles()
    {
        return $this->roles;
    }


    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }


    public function addRole(string $role)
    {
        $role = strtoupper($role);

        if ($role == UserRole::ROLE_DEFAULT)
        {
            return $this;
        }

        if (!in_array($role, $this->roles, true))
        {
            $this->roles[] = $role;
        }

        $this->roles = array_unique($this->roles);

        return $this;
    }


    public function removeRole(string $role)
    {
        $role = strtoupper($role);

        if ($role == UserRole::ROLE_DEFAULT)
        {
            return;
        }

        $key = array_search($role, $this->roles);

        if ($key === false)
        {
            return;
        }

        unset($this->roles[ $key ]);
        $this->roles = array_unique($this->roles);
    }


    public function getUsername()
    {
        return $this->email;
    }


    public function getPassword()
    {
        return $this->password;
    }


    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }


    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }


    public function getSalt()
    {
        return null; // nothing to do
    }


    public function getEmail()
    {
        return $this->email;
    }


    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }


    public function getStatus()
    {
        return $this->status;
    }


    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }


    public function getFirstName()
    {
        return $this->firstName;
    }


    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }


    public function getLastName()
    {
        return $this->lastName;
    }


    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }


    public function getType()
    {
        return $this->type;
    }


    public function setType($type)
    {
        $this->type = $type;

        if (UserType::SEARCH == $type)
        {
            $this->removeRole(UserRole::ROLE_PROPOSAL);
            $this->addRole(UserRole::ROLE_SEARCH);
        }
        else if (UserType::PROPOSAL == $type)
        {
            $this->removeRole(UserRole::ROLE_SEARCH);
            $this->addRole(UserRole::ROLE_PROPOSAL);
        }

        return $this;
    }


    public function getPlainPassword()
    {
        return $this->plainPassword;
    }


    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }


    public function setGender(?string $gender)
    {
        $this->gender = $gender;

        return $this;
    }


    public function getGender()
    {
        return $this->gender;
    }


    public function getBirthDate()
    {
        return $this->birthDate;
    }


    public function setBirthDate(\DateTime $birthDate = null)
    {
        $this->birthDate = $birthDate;

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


    public function setPhoneNumber(?string $phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }


    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }


    public function getTags() : Collection
    {
        return $this->tags;
    }


    public function setTags(Collection $tags)
    {
        $this->tags = $tags;

        return $this;
    }


    public function getAnnouncement()
    {
        return $this->announcement;
    }


    public function setAnnouncement(Announcement $announcement = null)
    {
        $this->announcement = $announcement;

        return $this;
    }


    public function getGroup()
    {
        return $this->group;
    }


    public function setGroup(Group $group = null)
    {
        $this->group = $group;

        return $this;
    }


    public function getPicture()
    {
        return $this->picture;
    }


    public function setPicture(ProfilePicture $picture = null)
    {
        $this->picture = $picture;

        return $this;
    }


    public function getAnnouncementPreference()
    {
        return $this->announcementPreference;
    }


    public function setAnnouncementPreference(AnnouncementPreference $announcementPreference = null)
    {
        $this->announcementPreference = $announcementPreference;

        return $this;
    }


    public function getUserPreference()
    {
        return $this->userPreference;
    }


    public function setUserPreference(UserPreference $userPreference = null)
    {
        $this->userPreference = $userPreference;

        return $this;
    }


    public function getLastLogin()
    {
        return $this->lastLogin;
    }


    public function setLastLogin(\DateTime $lastLogin = null)
    {
        $this->lastLogin = $lastLogin;
    }


    /**
     * Indicates if the user can be logged in the service
     *
     * @return bool <code>true</code> if the user has the status ENABLED or VACATION
     */
    public function isEnabled() : bool
    {
        return $this->status == UserStatus::ENABLED || $this->status == UserStatus::VACATION;
    }


    /**
     * Indicates if the user is active and not in vacation
     *
     * @return bool <code>true</code> if the user has the status ENABLED
     */
    public function isActive() : bool
    {
        return $this->status == UserStatus::ENABLED;
    }


    public function hasAnnouncement() : bool
    {
        return ($this->type == UserType::PROPOSAL) && !empty($this->announcement);
    }


    public function hasGroup() : bool
    {
        return ($this->type == UserType::SEARCH) && !empty($this->group);
    }

}
