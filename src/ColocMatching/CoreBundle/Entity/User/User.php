<?php

namespace ColocMatching\CoreBundle\Entity\User;

use ColocMatching\CoreBundle\Entity\AbstractEntity;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Service\VisitorInterface;
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
 *     @ORM\UniqueConstraint(name="UK_UNIQUE_PROFILE", columns={"profile_id"}),
 *     @ORM\UniqueConstraint(name="UK_USER_ANNOUNCEMENT_PREFERENCE", columns={"announcement_preference_id"}),
 *     @ORM\UniqueConstraint(name="UK_USER_PROFILE_PREFERENCE", columns={"user_preference_id"})
 * })
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\User\UserRepository")
 *
 * @author Dahiorus
 */
class User extends AbstractEntity implements UserInterface, Visitable
{
    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=64)
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
    private $status = UserConstants::STATUS_PENDING;

    /**
     * User roles
     * @var array
     * @ORM\Column(name="roles", type="array")
     */
    private $roles = [];

    /**
     * @var string
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    private $lastname;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=255, options={"default": "search"})
     */
    private $type = UserConstants::TYPE_SEARCH;

    /**
     * @var Announcement
     * @ORM\OneToOne(targetEntity="ColocMatching\CoreBundle\Entity\Announcement\Announcement",
     *   cascade={"remove"}, mappedBy="creator", fetch="LAZY")
     * @ORM\JoinColumn(name="announcement_id")
     */
    private $announcement;

    /**
     * @var Group
     * @ORM\OneToOne(targetEntity="ColocMatching\CoreBundle\Entity\Group\Group",
     *   cascade={"remove"}, mappedBy="creator", fetch="LAZY")
     * @ORM\JoinColumn(name="group_id", onDelete="SET NULL")
     */
    private $group;

    /**
     * @var ProfilePicture
     * @ORM\OneToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\ProfilePicture",
     *   cascade={"persist", "merge", "remove"}, fetch="LAZY")
     * @ORM\JoinColumn(name="picture_id", onDelete="SET NULL")
     */
    private $picture;

    /**
     * @var Profile
     * @ORM\OneToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\Profile",
     *   cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\JoinColumn(name="profile_id")
     */
    private $profile;

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
     * @param null|string $plainPassword
     * @param string $firstname
     * @param string $lastname
     */
    public function __construct(string $email, ?string $plainPassword, string $firstname, string $lastname)
    {
        $this->email = $email;
        $this->plainPassword = $plainPassword;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->setRoles(array ("ROLE_USER"));
        $this->profile = new Profile();
        $this->announcementPreference = new AnnouncementPreference();
        $this->userPreference = new UserPreference();
    }


    public function __toString()
    {
        $lastLogin = empty($this->lastLogin) ? null : $this->lastLogin->format(\DateTime::ISO8601);

        return parent::__toString() . "[email='" . $this->email . "', status='" . $this->status
            . "', roles={" . implode(",", $this->getRoles()) . "}, firstname='" . $this->firstname
            . "', lastname='" . $this->lastname . "', type='" . $this->type . ", lastLogin=" . $lastLogin . "]";
    }


    public function getRoles()
    {
        if ($this->type == UserConstants::TYPE_PROPOSAL)
        {
            return array_unique(array_merge(array ("ROLE_PROPOSAL"), $this->roles));
        }

        if ($this->type == UserConstants::TYPE_SEARCH)
        {
            return array_unique(array_merge(array ("ROLE_SEARCH"), $this->roles));
        }

        return array_unique($this->roles);
    }


    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }


    public function addRole(string $role)
    {
        $role = strtoupper($role);

        if ($role == UserConstants::ROLE_DEFAULT)
        {
            return $this;
        }

        if (!in_array($role, $this->roles, true))
        {
            $this->roles[] = $role;
        }

        return $this;
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
        // nothing to do
        return null;
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


    public function getFirstname()
    {
        return $this->firstname;
    }


    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }


    public function getLastname()
    {
        return $this->lastname;
    }


    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }


    public function getDisplayName()
    {
        return sprintf("%s %s", $this->firstname, $this->lastname);
    }


    public function getType()
    {
        return $this->type;
    }


    public function setType($type)
    {
        $this->type = $type;

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


    public function getProfile()
    {
        return $this->profile;
    }


    public function setProfile(Profile $profile = null)
    {
        $this->profile = $profile;

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
        return $this->status == UserConstants::STATUS_ENABLED || $this->status == UserConstants::STATUS_VACATION;
    }


    /**
     * Indicates if the user is active and not in vacation
     *
     * @return bool <code>true</code> if the user has the status ENABLED
     */
    public function isActive() : bool
    {
        return $this->status == UserConstants::STATUS_ENABLED;
    }


    public function hasAnnouncement() : bool
    {
        return $this->type == UserConstants::TYPE_PROPOSAL && !empty($this->announcement);
    }


    public function hasGroup() : bool
    {
        return $this->type == UserConstants::TYPE_SEARCH && !empty($this->group);
    }


    public function accept(VisitorInterface $visitor)
    {
        $visitor->visit($this);
    }

}
