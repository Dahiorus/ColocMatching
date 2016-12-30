<?php

namespace Appartoo\CoreBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="Appartoo\CoreBundle\Repository\User\UserRepository")
 * @JMS\ExclusionPolicy("ALL")
 */
class User implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;
    
    /**
     * @var string
     * 
     * @ORM\Column(name="password", type="string", length=64)
     */
    private $password;
    
    /**
     *  Not persisted
     * 
     * @var string
     * 
     * @Assert\NotBlank()
     * @Assert\Length(min=8, max=4096)
     */
    private $plainPassword;
    
    /**
     * @var boolean
     * 
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled = false;
    
    /**
     * @var string
     * 
     * @ORM\Column(name="gender", type="string", options={"default": "unknown"}) 
     */
    private $gender = UserConstants::GENDER_UNKNOWN;
    
    /**
     * @var string
     * 
     * @ORM\Column(name="phonenumber", type="string", length=10, nullable=true) 
     */
    private $phoneNumber;
    
    /**
     * @var string
     *
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
     *
     * @ORM\Column(name="type", type="string", length=255, options={"default": "search"})
     */
    private $type = UserConstants::TYPE_SEARCH;
    
    /**
     * @var \Appartoo\CoreBundle\Entity\Announcement\Announcement
     * @ORM\OneToOne(targetEntity="\Appartoo\CoreBundle\Entity\Announcement\Announcement")
     * @ORM\JoinColumn(name="announcementId", referencedColumnName="id")
     */
    private $announcement;
    
    
    public function __construct(string $email, string $password, string $firstname, string $lastname) {
        $this->email = $email;
        $this->plainPassword = $password;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
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
        // TODO something
    }
    
    
    public function getSalt() {
        // nothing to do
        return null;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set phoneNumber
     *
     * @param string $phoneNumber
     *
     * @return User
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return User
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    
    /**
     * Get plainPassword
     * 
     * @return string
     */
    public function getPlainPassword() {
        return $this->plainPassword;
    }
    
    
    /**
     * Set plainPassword
     * 
     * @param string $plainPassword
     * @return User
     */
    public function setPlainPassword($plainPassword) {
        $this->plainPassword = $plainPassword;
        
        return $this;
    }
    
    
    /**
     * Set announcement
     *
     * @param \Appartoo\CoreBundle\Entity\Announcement\Announcement $announcement
     *
     * @return User
     */
    public function setAnnouncement(\Appartoo\CoreBundle\Entity\Announcement\Announcement $announcement = null)
    {
        $this->announcement = $announcement;

        return $this;
    }

    /**
     * Get announcement
     *
     * @return \Appartoo\CoreBundle\Entity\Announcement\Announcement
     */
    public function getAnnouncement()
    {
        return $this->announcement;
    }
}
