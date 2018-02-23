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
 * @SWG\Definition(definition="Profile")
 */
class ProfileDto extends AbstractDto
{

    /**
     * @var string
     * @Serializer\Expose
     * @Assert\Choice(choices={"unknown", "male", "female"}, strict=true)
     * @SWG\Property(description="The gender",
     *   enum={ "male", "female", "unknown" }, default="unknown")
     */
    private $gender = ProfileConstants::GENDER_UNKNOWN;

    /**
     * @var \DateTime
     * @Assert\Date()
     * @Serializer\Expose
     * @Serializer\SerializedName("birthDate")
     * @SWG\Property(description="The birth date", format="date")
     */
    private $birthDate;

    /**
     * @var string
     * @Serializer\Expose
     * @SWG\Property(description="Profile description")
     */
    private $description;

    /**
     * @var string
     * @Serializer\Expose
     * @Serializer\SerializedName("phoneNumber")
     * @SWG\Property(description="The phone number")
     */
    private $phoneNumber;

    /**
     * @var boolean
     * @Serializer\Expose
     * @Assert\Type("bool")
     * @SWG\Property(description="Is smoker", default=false)
     */
    private $smoker = false;

    /**
     * @var boolean
     * @Serializer\Expose
     * @Serializer\SerializedName("houseProud")
     * @Assert\Type("bool")
     * @SWG\Property(description="Is house proud", default=false)
     */
    private $houseProud = false;

    /**
     * @var boolean
     * @Serializer\Expose
     * @Assert\Type("bool")
     * @SWG\Property(description="Is cook", default=false)
     */
    private $cook = false;

    /**
     * @var boolean
     * @Serializer\Expose
     * @Serializer\SerializedName("hasJob")
     * @Assert\Type("bool")
     * @SWG\Property(description="Has job", default=false)
     */
    private $hasJob = false;

    /**
     * @var string
     * @Serializer\Expose
     * @Assert\Choice(choices={"meat_eater", "vegetarian", "vegan", "unknown"}, strict=true)
     * @SWG\Property(description="The diet",
     *   enum={ "meat_eater", "vegetarian", "vegan", "unknown" }, default="unknown")
     */
    private $diet = ProfileConstants::DIET_UNKNOWN;

    /**
     * @var string
     * @Serializer\Expose
     * @Serializer\SerializedName("maritalStatus")
     * @Assert\Choice(choices={"couple", "single", "unknown"}, strict=true)
     * @SWG\Property(description="The martial status",
     *   enum={ "couple", "single", "unknown" }, default="unknown")
     */
    private $maritalStatus = ProfileConstants::MARITAL_UNKNOWN;

    /**
     * @var string
     * @Serializer\Expose
     * @Serializer\SerializedName("socialStatus")
     * @Assert\Choice(choices={"student", "worker", "unknown"}, strict=true)
     * @SWG\Property(description="The social status",
     *   enum={ "student", "worker", "unknown" }, default="unknown")
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
