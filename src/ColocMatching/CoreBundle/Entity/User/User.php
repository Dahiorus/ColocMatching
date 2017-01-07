<?php

namespace ColocMatching\CoreBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;

/**
 * User
 *
 * @ORM\Table(
 *   name="app_user",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="app_user_email_unique", columns={"email"}),
 *     @ORM\UniqueConstraint(name="app_user_announcement_unique", columns={"announcement_id"})
 * })
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\User\UserRepository")
 * @JMS\ExclusionPolicy("ALL")
 */
class User implements UserInterface
{
    /**
     * User Id
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     */
    private $id;
    
    /**
     * User email
     *
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @JMS\Expose()
     * @Assert\NotBlank()
     * @Assert\Email(strict=true)
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
     */
    private $plainPassword;
    
    /**
     * User is enabled
     *
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", options={"default": false})
     * @JMS\Expose()
     * @Assert\Type("bool")
     */
    private $enabled = false;
    
    /**
     * User gender
     *
     * @var string
     *
     * @ORM\Column(name="gender", type="string", options={"default": "unknown"})
     * @JMS\Expose()
     * @Assert\Choice({"unknown", "male", "female"})
     */
    private $gender = UserConstants::GENDER_UNKNOWN;
    
    /**
     * User phone number
     *
     * @var string
     *
     * @ORM\Column(name="phonenumber", type="string", length=10, nullable=true)
     * @JMS\Expose()
     */
    private $phoneNumber;
    
    /**
     * User firstname
     *
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     * @JMS\Expose()
     * @Assert\NotBlank()
     */
    private $firstname;

    /**
     * Usre lastname
     *
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     * @JMS\Expose()
     * @Assert\NotBlank()
     */
    private $lastname;

    /**
     * User type
     *
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, options={"default": "search"})
     * @JMS\Expose()
     * @Assert\Choice({"search", "proposal"})
     */
    private $type = UserConstants::TYPE_SEARCH;
    
    /**
     * User announcement
     *
     * @var Announcement
     *
     * @ORM\OneToOne(targetEntity="\ColocMatching\CoreBundle\Entity\Announcement\Announcement", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="announcement_id", referencedColumnName="id")
     */
    private $announcement;
    
    
    /**
     * User constructor
     */
    public function __construct() {
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    
    public function getUsername() {
        return $this->email;
    }
    
    
    public function getPassword() {
        return $this->password;
    }
    
    
    public function getRoles() {
        return array('ROLE_USER');
    }
    
    
    public function eraseCredentials() {
        $this->plainPassword = null;
    }
    
    
    public function getSalt() {
        // nothing to do
        return null;
    }

    
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    
    public function getEmail()
    {
        return $this->email;
    }

    
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    
    public function isEnabled()
    {
        return $this->enabled;
    }

    
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    
    public function getGender()
    {
        return $this->gender;
    }

    
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

   
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    
    public function getFirstname()
    {
        return $this->firstname;
    }

    
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    
    public function getLastname()
    {
        return $this->lastname;
    }

    
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    
    public function getType()
    {
        return $this->type;
    }
    
    
    
    public function getPlainPassword() {
        return $this->plainPassword;
    }
    
    
    
    public function setPlainPassword($plainPassword) {
        $this->plainPassword = $plainPassword;
        
        return $this;
    }
    
    
    
    public function setAnnouncement(Announcement $announcement = null)
    {
        $this->announcement = $announcement;

        return $this;
    }

    
    public function getAnnouncement()
    {
        return $this->announcement;
    }
    
    
    public function __toString() {
        return "User [id=$this->id, email=$this->email, enabled=$this->enabled, gender=$this->gender,"
            . "phoneNumber=$this->phoneNumber, firstname=$this->firstname, lastname=$this->lastname, "
            . "type=$this->type, announcement=$this->announcement]";
    }

}
