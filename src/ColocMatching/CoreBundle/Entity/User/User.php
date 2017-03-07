<?php

namespace ColocMatching\CoreBundle\Entity\User;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(
 *   name="app_user",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="app_user_email_unique", columns={"email"}),
 *     @ORM\UniqueConstraint(name="app_user_announcement_unique", columns={"announcement_id"}),
 *     @ORM\UniqueConstraint(name="app_user_picture_unique", columns={"picture_id"}),
 *     @ORM\UniqueConstraint(name="app_user_profile_unique", columns={"profile_id"})
 * })
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\User\UserRepository")
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(
 *   definition="User", required={ "email", "firstname", "lastname" }
 * )
 */
class User implements UserInterface {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     * @SWG\Property(description="User id", readOnly=true)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @JMS\Expose()
     * @Assert\NotBlank()
     * @Assert\Email(strict=true)
     * @SWG\Property(description="User email")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=64)
     */
    private $password;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"Create", "FullUpdate"})
     * @Assert\Length(min=8, max=4096)
     * @SWG\Property(description="User password (used only in POST, PUT, PATCH operations)")
     */
    private $plainPassword;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", options={"default": false})
     * @JMS\Expose()
     * @Assert\Type("bool")
     * @SWG\Property(description="User is enabled")
     */
    private $enabled = false;

    /**
     * User roles
     *
     * @var array
     *
     * @ORM\Column(name="roles", type="array")
     */
    private $roles = [ ];

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     * @JMS\Expose()
     * @Assert\NotBlank()
     * @SWG\Property(description="User first name")
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     * @JMS\Expose()
     * @Assert\NotBlank()
     * @SWG\Property(description="User last name")
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, options={"default": "search"})
     * @JMS\Expose()
     * @Assert\Choice(choices={"search", "proposal"}, strict=true)
     * @SWG\Property(
     *   description="User type",
     *   enum={"search", "proposal"}, default="search"
     * )
     */
    private $type = UserConstants::TYPE_SEARCH;

    /**
     * User announcement
     *
     * @var Announcement
     *
     * @ORM\OneToOne(targetEntity="ColocMatching\CoreBundle\Entity\Announcement\Announcement",
     *   cascade={"persist", "remove"}, mappedBy="creator", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="announcement_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $announcement;

    /**
     * User picture
     *
     * @var ProfilePicture
     *
     * @ORM\OneToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\ProfilePicture",
     *   cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\JoinColumn(name="picture_id", referencedColumnName="id", onDelete="SET NULL")
     * @Assert\Valid()
     */
    private $picture;

    /**
     * User profile
     *
     * @var Profile
     * @ORM\OneToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\Profile",
     *   cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     * @Assert\Valid()
     */
    private $profile;


    /**
     * User constructor
     */
    public function __construct() {
        $this->setRoles([ "ROLE_USER"]);
        $this->profile = new Profile();
    }


    public function __toString() {
        return sprintf(
            "User [id: %d, email: '%s', enabled: %d, roles: [%s], firstname: '%s', lastname: '%s', type: '%s']",
            $this->id, $this->email, $this->enabled, implode(",", $this->getRoles()), $this->firstname, $this->lastname,
            $this->type);
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }


    public function getUsername() {
        return $this->email;
    }


    public function getPassword() {
        return $this->password;
    }


    public function getRoles() {
        return array_unique($this->roles);
    }


    public function setRoles(array $roles) {
        $this->roles = $roles;

        return $this;
    }


    public function addRole(string $role) {
        $role = strtoupper($role);

        if ($role == UserConstants::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }


    public function eraseCredentials() {
        $this->plainPassword = null;
    }


    public function getSalt() {
        // nothing to do
        return null;
    }


    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }


    public function getEmail() {
        return $this->email;
    }


    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }


    public function setEnabled($enabled) {
        $this->enabled = $enabled;

        return $this;
    }


    public function isEnabled() {
        return $this->enabled;
    }


    public function setFirstname($firstname) {
        $this->firstname = $firstname;

        return $this;
    }


    public function getFirstname() {
        return $this->firstname;
    }


    public function setLastname($lastname) {
        $this->lastname = $lastname;

        return $this;
    }


    public function getLastname() {
        return $this->lastname;
    }


    public function setType($type) {
        $this->type = $type;

        return $this;
    }


    public function getType() {
        return $this->type;
    }


    public function getPlainPassword() {
        return $this->plainPassword;
    }


    public function setPlainPassword($plainPassword) {
        $this->plainPassword = $plainPassword;

        return $this;
    }


    public function setAnnouncement(Announcement $announcement = null) {
        $this->announcement = $announcement;

        return $this;
    }


    public function getAnnouncement() {
        return $this->announcement;
    }


    public function getPicture() {
        return $this->picture;
    }


    public function setPicture(ProfilePicture $picture = null) {
        $this->picture = $picture;
        return $this;
    }


    public function getProfile() {
        return $this->profile;
    }


    public function setProfile(Profile $profile = null) {
        $this->profile = $profile;
        return $this;
    }

}
