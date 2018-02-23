<?php

namespace ColocMatching\CoreBundle\DTO\User;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\Entity\User\ProfileConstants;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Entity\User\UserPreference;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

class UserPreferenceDto extends AbstractDto
{
    /**
     * User type filter
     * @var string
     *
     * @Serializer\Expose
     * @Assert\Choice(choices={ UserConstants::TYPE_SEARCH, UserConstants::TYPE_PROPOSAL },
     *   strict=true)
     * @SWG\Property(enum={ "search", "proposal" })
     */
    private $type;

    /**
     * User gender filter
     * @var string
     *
     * @Serializer\Expose
     * @Assert\Choice(choices={ ProfileConstants::GENDER_MALE, ProfileConstants::GENDER_FEMALE,
     *   ProfileConstants::GENDER_UNKNOWN }, strict=true)
     * @SWG\Property(enum={ "male", "female" })
     */
    private $gender;

    /**
     * Age start range filter
     * @var integer
     *
     * @Serializer\SerializedName("ageStart")
     * @Serializer\Expose
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @SWG\Property
     */
    private $ageStart;

    /**
     * Age end range filter
     * @var integer
     *
     * @Serializer\SerializedName("ageEnd")
     * @Serializer\Expose
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     * @SWG\Property
     */
    private $ageEnd;

    /**
     * Only with description
     * @var boolean
     *
     * @Serializer\SerializedName("withDescription")
     * @Assert\Type("boolean")
     * @Serializer\Expose
     * @SWG\Property(default=false)
     */
    private $withDescription = false;

    /**
     * Is smoker filter
     * @var boolean
     *
     * @Serializer\SerializedName("smoker")
     * @Serializer\Expose
     * @Assert\Type("boolean")
     * @SWG\Property
     */
    private $smoker;

    /**
     * Has job filter
     * @var boolean
     *
     * @Serializer\SerializedName("hasJob")
     * @Serializer\Expose
     * @Assert\Type("boolean")
     * @SWG\Property
     */
    private $hasJob;

    /**
     * Diet filter
     * @var string
     *
     * @Serializer\Expose
     * @Assert\Choice(choices={ ProfileConstants::DIET_MEAT_EATER, ProfileConstants::DIET_VEGETARIAN,
     *   ProfileConstants::DIET_VEGAN, ProfileConstants::DIET_UNKNOWN }, strict=true)
     * @SWG\Property(enum={ "meat_eater", "vegetarian", "vegan" })
     */
    private $diet;

    /**
     * Social status filter
     * @var string
     *
     * @Serializer\SerializedName("socialStatus")
     * @Serializer\Expose
     * @Assert\Choice(choices={ProfileConstants::SOCIAL_STUDENT, ProfileConstants::SOCIAL_WORKER,
     *   ProfileConstants::SOCIAL_UNKNOWN}, strict=true)
     * @SWG\Property(enum={ "student", "worker" })
     */
    private $socialStatus;

    /**
     * Marital status filter
     * @var string
     *
     * @Serializer\SerializedName("maritalStatus")
     * @Serializer\Expose
     * @Assert\Choice(choices={ ProfileConstants::MARITAL_COUPLE, ProfileConstants::MARITAL_SINGLE,
     *   ProfileConstants::MARITAL_UNKNOWN })
     * @SWG\Property(enum={ "couple", "single" })
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


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @param string $type
     *
     * @return UserPreferenceDto
     */
    public function setType(?string $type) : UserPreferenceDto
    {
        $this->type = $type;

        return $this;
    }


    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }


    /**
     * @param string $gender
     *
     * @return UserPreferenceDto
     */
    public function setGender(?string $gender) : UserPreferenceDto
    {
        $this->gender = $gender;

        return $this;
    }


    /**
     * @return int
     */
    public function getAgeStart()
    {
        return $this->ageStart;
    }


    /**
     * @param int $ageStart
     *
     * @return UserPreferenceDto
     */
    public function setAgeStart(?int $ageStart) : UserPreferenceDto
    {
        $this->ageStart = $ageStart;

        return $this;
    }


    /**
     * @return int
     */
    public function getAgeEnd()
    {
        return $this->ageEnd;
    }


    /**
     * @param int $ageEnd
     *
     * @return UserPreferenceDto
     */
    public function setAgeEnd(?int $ageEnd) : UserPreferenceDto
    {
        $this->ageEnd = $ageEnd;

        return $this;
    }


    /**
     * @return bool
     */
    public function withDescription()
    {
        return $this->withDescription;
    }


    /**
     * @param bool $withDescription
     *
     * @return UserPreferenceDto
     */
    public function setWithDescription(bool $withDescription) : UserPreferenceDto
    {
        $this->withDescription = $withDescription;

        return $this;
    }


    /**
     * @return bool
     */
    public function isSmoker()
    {
        return $this->smoker;
    }


    /**
     * @param bool $smoker
     *
     * @return UserPreferenceDto
     */
    public function setSmoker(?bool $smoker) : UserPreferenceDto
    {
        $this->smoker = $smoker;

        return $this;
    }


    /**
     * @return bool
     */
    public function hasJob()
    {
        return $this->hasJob;
    }


    /**
     * @param bool $hasJob
     *
     * @return UserPreferenceDto
     */
    public function setHasJob(?bool $hasJob) : UserPreferenceDto
    {
        $this->hasJob = $hasJob;

        return $this;
    }


    /**
     * @return string
     */
    public function getDiet()
    {
        return $this->diet;
    }


    /**
     * @param string $diet
     *
     * @return UserPreferenceDto
     */
    public function setDiet(?string $diet) : UserPreferenceDto
    {
        $this->diet = $diet;

        return $this;
    }


    /**
     * @return string
     */
    public function getSocialStatus()
    {
        return $this->socialStatus;
    }


    /**
     * @param string $socialStatus
     *
     * @return UserPreferenceDto
     */
    public function setSocialStatus(?string $socialStatus) : UserPreferenceDto
    {
        $this->socialStatus = $socialStatus;

        return $this;
    }


    /**
     * @return string
     */
    public function getMaritalStatus()
    {
        return $this->maritalStatus;
    }


    /**
     * @param string $maritalStatus
     *
     * @return UserPreferenceDto
     */
    public function setMaritalStatus(?string $maritalStatus) : UserPreferenceDto
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }


    public function getEntityClass() : string
    {
        return UserPreference::class;
    }
}