<?php

namespace ColocMatching\CoreBundle\Entity\User;

use ColocMatching\CoreBundle\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Profile
 *
 * @ORM\Table(name="app_profile")
 * @ORM\Entity
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="profiles")
 */
class Profile extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", options={"default": "unknown"})
     */
    private $gender = ProfileConstants::GENDER_UNKNOWN;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birth_date", type="date", nullable=true)
     */
    private $birthDate;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="phonenumber", type="string", length=10, nullable=true)
     */
    private $phoneNumber;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_smoker", type="boolean", options={"default": false}, nullable=true)
     */
    private $smoker = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_house_proud", type="boolean", options={"default": false}, nullable=true)
     */
    private $houseProud = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_cook", type="boolean", options={"default": false}, nullable=true)
     */
    private $cook = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_job", type="boolean", options={"default": false}, nullable=true)
     */
    private $hasJob = false;

    /**
     * @var string
     *
     * @ORM\Column(name="diet", type="string", options={"default": "unknown"})
     */
    private $diet = ProfileConstants::DIET_UNKNOWN;

    /**
     * @var string
     *
     * @ORM\Column(name="marital_status", type="string", options={"default": "unknown"})
     */
    private $maritalStatus = ProfileConstants::MARITAL_UNKNOWN;

    /**
     * @var string
     *
     * @ORM\Column(name="social_status", type="string", options={"default": "unknown"})
     */
    private $socialStatus = ProfileConstants::SOCIAL_UNKNOWN;


    public function __toString()
    {
        $birthDate = empty($this->birthDate) ? "" : $this->birthDate->format(\DateTime::ISO8601);

        return parent::__toString() . "[gender = " . $this->gender . ", birthDate = " . $birthDate
            . ", description = " . $this->description . ", phoneNumber = " . $this->phoneNumber
            . ", smoker = " . $this->smoker . ", houseProud = " . $this->houseProud . ", cook = " . $this->cook
            . ", hasJob = " . $this->hasJob . ", diet = " . $this->diet . ", maritalStatus = " . $this->maritalStatus
            . ", socialStatus = " . $this->socialStatus . "]";
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


    public function isSmoker()
    {
        return $this->smoker;
    }


    public function setSmoker(?bool $smoker)
    {
        $this->smoker = $smoker;

        return $this;
    }


    public function isHouseProud()
    {
        return $this->houseProud;
    }


    public function setHouseProud(?bool $houseProud)
    {
        $this->houseProud = $houseProud;

        return $this;
    }


    public function isCook()
    {
        return $this->cook;
    }


    public function setCook(?bool $cook)
    {
        $this->cook = $cook;

        return $this;
    }


    public function hasJob()
    {
        return $this->hasJob;
    }


    public function setHasJob(?bool $hasJob)
    {
        $this->hasJob = $hasJob;

        return $this;
    }


    public function getDiet()
    {
        return $this->diet;
    }


    public function setDiet(?string $diet)
    {
        $this->diet = $diet;

        return $this;
    }


    public function getMaritalStatus()
    {
        return $this->maritalStatus;
    }


    public function setMaritalStatus(?string $maritalStatus)
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }


    public function getSocialStatus()
    {
        return $this->socialStatus;
    }


    public function setSocialStatus(?string $socialStatus)
    {
        $this->socialStatus = $socialStatus;

        return $this;
    }

}