<?php

namespace ColocMatching\CoreBundle\Entity\User;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * Profile
 *
 * @ORM\Table(name="app_profile")
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Profile")
 */
class Profile implements EntityInterface {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     * @SWG\Property(description="Profile id", readOnly=true)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", options={"default": "unknown"})
     * @JMS\Expose()
     * @Assert\Choice(choices={"unknown", "male", "female"}, strict=true)
     * @SWG\Property(description="The gender",
     *   enum={ "male", "female", "unknown" }, default="unknown")
     */
    private $gender = ProfileConstants::GENDER_UNKNOWN;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birth_date", type="date", nullable=true)
     * @Assert\Date()
     * @JMS\Expose()
     * @JMS\SerializedName("birthDate")
     * @SWG\Property(description="The birth date", format="date")
     */
    private $birthDate;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @JMS\Expose()
     * @SWG\Property(description="Profile description")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="phonenumber", type="string", length=10, nullable=true)
     * @JMS\Expose()
     * @JMS\SerializedName("phoneNumber")
     * @SWG\Property(description="The phone number")
     */
    private $phoneNumber;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_smoker", type="boolean", options={"default": false}, nullable=true)
     * @JMS\Expose()
     * @Assert\Type("bool")
     * @SWG\Property(description="Is smoker", default=false)
     */
    private $smoker = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_house_proud", type="boolean", options={"default": false}, nullable=true)
     * @JMS\Expose()
     * @JMS\SerializedName("houseProud")
     * @Assert\Type("bool")
     * @SWG\Property(description="Is house proud", default=false)
     */
    private $houseProud = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_cook", type="boolean", options={"default": false}, nullable=true)
     * @JMS\Expose()
     * @Assert\Type("bool")
     * @SWG\Property(description="Is cook", default=false)
     */
    private $cook = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_job", type="boolean", options={"default": false}, nullable=true)
     * @JMS\Expose()
     * @JMS\SerializedName("hasJob")
     * @Assert\Type("bool")
     * @SWG\Property(description="Has job", default=false)
     */
    private $hasJob = false;

    /**
     * @var string
     *
     * @ORM\Column(name="diet", type="string", options={"default": "unknown"})
     * @JMS\Expose()
     * @Assert\Choice(choices={"meat_eater", "vegetarian", "vegan", "unknown"}, strict=true)
     * @SWG\Property(description="The diet",
     *   enum={ "meat_eater", "vegetarian", "vegan", "unknown" }, default="unknown")
     */
    private $diet = ProfileConstants::DIET_UNKNOWN;

    /**
     * @var string
     *
     * @ORM\Column(name="marital_status", type="string", options={"default": "unknown"})
     * @JMS\Expose()
     * @JMS\SerializedName("maritalStatus")
     * @Assert\Choice(choices={"couple", "single", "unknown"}, strict=true)
     * @SWG\Property(description="The martial status",
     *   enum={ "couple", "single", "unknown" }, default="unknown")
     */
    private $maritalStatus = ProfileConstants::MARITAL_UNKNOWN;

    /**
     * @var string
     *
     * @ORM\Column(name="social_status", type="string", options={"default": "unknown"})
     * @JMS\Expose()
     * @JMS\SerializedName("socialStatus")
     * @Assert\Choice(choices={"student", "worker", "unknown"}, strict=true)
     * @SWG\Property(description="The social status",
     *   enum={ "student", "worker", "unknown" }, default="unknown")
     */
    private $socialStatus = ProfileConstants::SOCIAL_UNKNOWN;


    public function __toString() {
        $birthDate = empty($this->birthDate) ? "" : $this->birthDate->format(\DateTime::ISO8601);

        return sprintf(
            "Profile [id: %d, gender: '%s', birthDate: '%s', description: '%s', phoneNumber: '%s', smoker: '%d', houseProud: '%s', cook: '%s', hasJob: %d, diet: '%s', maritalStatus: '%s', socialStatus: '%s']",
            $this->id, $this->gender, $birthDate, $this->description, $this->phoneNumber, $this->smoker,
            $this->houseProud, $this->cook, $this->hasJob, $this->diet, $this->maritalStatus, $this->socialStatus);
    }


    /**
     * The age
     *
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("age")
     * @JMS\Type("integer")
     *
     * @return int
     */
    public function getAge(): int {
        if (!empty($this->birthDate)) {
            return $this->birthDate->diff(new \DateTime('today'))->y;
        }

        return 0;
    }


    public function getId() {
        return $this->id;
    }


    public function setGender(string $gender = null) {
        $this->gender = $gender;

        return $this;
    }


    public function getGender() {
        return $this->gender;
    }


    public function getBirthDate() {
        return $this->birthDate;
    }


    public function setBirthDate(\DateTime $birthDate = null) {
        $this->birthDate = $birthDate;
        return $this;
    }


    public function getDescription() {
        return $this->description;
    }


    public function setDescription(string $description = null) {
        $this->description = $description;
        return $this;
    }


    public function setPhoneNumber(string $phoneNumber = null) {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }


    public function getPhoneNumber() {
        return $this->phoneNumber;
    }


    public function isSmoker() {
        return $this->smoker;
    }


    public function setSmoker(bool $smoker = null) {
        $this->smoker = $smoker;
        return $this;
    }


    public function isHouseProud() {
        return $this->houseProud;
    }


    public function setHouseProud(bool $houseProud) {
        $this->houseProud = $houseProud;
        return $this;
    }


    public function isCook() {
        return $this->cook;
    }


    public function setCook(bool $cook) {
        $this->cook = $cook;
        return $this;
    }


    public function hasJob() {
        return $this->hasJob;
    }


    public function setHasJob(bool $hasJob) {
        $this->hasJob = $hasJob;
        return $this;
    }


    public function getDiet() {
        return $this->diet;
    }


    public function setDiet(string $diet = null) {
        $this->diet = $diet;
        return $this;
    }


    public function getMaritalStatus() {
        return $this->maritalStatus;
    }


    public function setMaritalStatus(string $maritalStatus = null) {
        $this->maritalStatus = $maritalStatus;
        return $this;
    }


    public function getSocialStatus() {
        return $this->socialStatus;
    }


    public function setSocialStatus(string $socialStatus = null) {
        $this->socialStatus = $socialStatus;
        return $this;
    }

}