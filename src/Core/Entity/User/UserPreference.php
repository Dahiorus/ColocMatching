<?php

namespace App\Core\Entity\User;

use App\Core\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * AnnouncementPreference
 *
 * @ORM\Table(name="user_preference")
 * @ORM\Entity
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="user_preferences")
 */
class UserPreference extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="user_type", type="string", nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", nullable=true)
     */
    private $gender;

    /**
     * @var integer
     *
     * @ORM\Column(name="age_start", type="integer", nullable=true)
     */
    private $ageStart;

    /**
     * @var integer
     *
     * @ORM\Column(name="age_end", type="integer", nullable=true)
     */
    private $ageEnd;

    /**
     * @var boolean
     *
     * @ORM\Column(name="with_description", type="boolean", nullable=true)
     */
    private $withDescription = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_smoker", type="boolean", nullable=true)
     */
    private $smoker;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_job", type="boolean", nullable=true)
     */
    private $hasJob;

    /**
     * @var string
     *
     * @ORM\Column(name="diet", type="string", nullable=true)
     */
    private $diet;

    /**
     * @var string
     *
     * @ORM\Column(name="social_status", type="string", nullable=true)
     */
    private $socialStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="marital_status", type="string", nullable=true)
     */
    private $maritalStatus;


    public function __toString() : string
    {
        return parent::__toString() . "[type = " . $this->type . ", gender = " . $this->gender
            . ", ageStart = " . $this->ageStart . ", ageEnd = " . $this->ageEnd
            . ", withDescription = " . $this->withDescription . ", smoker = " . $this->smoker
            . ", hasJob = " . $this->hasJob . ", diet = " . $this->diet . ", socialStatus = " . $this->socialStatus
            . ", maritalStatus = " . $this->maritalStatus . "]";
    }


    public function getType()
    {
        return $this->type;
    }


    public function setType(?string $type)
    {
        $this->type = $type;

        return $this;
    }


    public function getGender()
    {
        return $this->gender;
    }


    public function setGender(?string $gender)
    {
        $this->gender = $gender;

        return $this;
    }


    public function getAgeStart()
    {
        return $this->ageStart;
    }


    public function setAgeStart(?int $ageStart)
    {
        $this->ageStart = $ageStart;

        return $this;
    }


    public function getAgeEnd()
    {
        return $this->ageEnd;
    }


    public function setAgeEnd(?int $ageEnd)
    {
        $this->ageEnd = $ageEnd;

        return $this;
    }


    public function withDescription()
    {
        return $this->withDescription;
    }


    public function setWithDescription(?bool $withDescription)
    {
        $this->withDescription = $withDescription;

        return $this;
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


    public function getSocialStatus()
    {
        return $this->socialStatus;
    }


    public function setSocialStatus(?string $socialStatus)
    {
        $this->socialStatus = $socialStatus;

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

}