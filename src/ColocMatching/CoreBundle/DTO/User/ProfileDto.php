<?php

namespace ColocMatching\CoreBundle\DTO\User;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Entity\User\ProfileConstants;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("ALL")
 */
class ProfileDto extends AbstractDto
{

    /**
     * The gender
     * @var string
     *
     * @Serializer\Expose
     * @Assert\Choice(choices={"unknown", "male", "female"}, strict=true)
     * @SWG\Property(property="gender", type="string", default="unknown")
     */
    private $gender = ProfileConstants::GENDER_UNKNOWN;

    /**
     * The birth date
     * @var \DateTime
     *
     * @Assert\Date
     * @Serializer\Expose
     * @Serializer\SerializedName("birthDate")
     * @SWG\Property(property="birthDate", type="string", format="date", example="1990-01-01")
     */
    private $birthDate;

    /**
     * Profile description
     * @var string
     *
     * @Serializer\Expose
     * @SWG\Property(property="description", type="string")
     */
    private $description;

    /**
     * The phone number
     * @var string
     *
     * @Serializer\Expose
     * @Serializer\SerializedName("phoneNumber")
     * @SWG\Property(property="phoneNumber", type="string")
     */
    private $phoneNumber;

    /**
     * Is smoker
     * @var boolean
     *
     * @Serializer\Expose
     * @Assert\Type("bool")
     * @SWG\Property(property="smoker", type="boolean", default=false)
     */
    private $smoker = false;

    /**
     * Is a house proud
     * @var boolean
     *
     * @Serializer\Expose
     * @Serializer\SerializedName("houseProud")
     * @Assert\Type("bool")
     * @SWG\Property(property="houseProud", type="boolean", default=false)
     */
    private $houseProud = false;

    /**
     * Is a cook
     * @var boolean
     *
     * @Serializer\Expose
     * @Assert\Type("bool")
     * @SWG\Property(property="cook", type="boolean", default=false)
     */
    private $cook = false;

    /**
     * Has a job
     * @var boolean
     *
     * @Serializer\Expose
     * @Serializer\SerializedName("hasJob")
     * @Assert\Type("bool")
     * @SWG\Property(property="hasJob", type="boolean", default=false)
     */
    private $hasJob = false;

    /**
     * The diet
     * @var string
     *
     * @Serializer\Expose
     * @Assert\Choice(choices={"meat_eater", "vegetarian", "vegan", "unknown"}, strict=true)
     * @SWG\Property(property="diet", type="string", default="unknown")
     */
    private $diet = ProfileConstants::DIET_UNKNOWN;

    /**
     * The marital status
     * @var string
     *
     * @Serializer\Expose
     * @Serializer\SerializedName("maritalStatus")
     * @Assert\Choice(choices={"couple", "single", "unknown"}, strict=true)
     * @SWG\Property(property="maritalStatus", type="string", default="unknown")
     */
    private $maritalStatus = ProfileConstants::MARITAL_UNKNOWN;

    /**
     * The social status
     * @var string
     *
     * @Serializer\Expose
     * @Serializer\SerializedName("socialStatus")
     * @Assert\Choice(choices={"student", "worker", "unknown"}, strict=true)
     * @SWG\Property(property="socialStatus", type="string", default="unknown")
     */
    private $socialStatus = ProfileConstants::SOCIAL_UNKNOWN;


    public function __toString() : string
    {
        $birthDate = empty($this->birthDate) ? null : $this->birthDate->format(\DateTime::ISO8601);

        return parent::__toString() . "[gender = " . $this->gender . ", birthDate = " . $birthDate
            . ", description = " . $this->description . ", phoneNumber = " . $this->phoneNumber
            . ", smoker = " . $this->smoker . ", houseProud = " . $this->houseProud . ", cook = " . $this->cook
            . ", hasJob = " . $this->hasJob . ", diet = " . $this->diet . ", maritalStatus = " . $this->maritalStatus
            . ", socialStatus = " . $this->socialStatus . "]";
    }


    /**
     * Gets the user age
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("age")
     * @Serializer\Type("integer")
     * @SWG\Property(property="age", type="integer", description="Calculated age", readOnly=true)
     *
     * @return int
     */
    public function getAge() : int
    {
        if (!empty($this->birthDate))
        {
            return $this->birthDate->diff(new \DateTime('today'))->y;
        }

        return 0;
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


    public function getEntityClass() : string
    {
        return Profile::class;
    }

}
