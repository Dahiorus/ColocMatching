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


    public function __toString() : string
    {
        return parent::__toString() . "[type = " . $this->type . ", gender = " . $this->gender
            . ", ageStart = " . $this->ageStart . ", ageEnd = " . $this->ageEnd
            . ", withDescription = " . $this->withDescription . "]";
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

}
