<?php

namespace ColocMatching\CoreBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;
use ColocMatching\CoreBundle\Entity\EntityInterface;

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
     * @var string
     *
     * @ORM\Column(name="phonenumber", type="string", length=10, nullable=true)
     * @JMS\Expose()
     */
    private $phoneNumber;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_smoker", type="boolean", options={"default": false})
     * @JMS\Expose()
     * @Assert\Type("bool")
     * @SWG\Property(description="Is smoker", default=false)
     */
    private $smoker = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_house_proud", type="boolean", options={"default": false})
     * @JMS\Expose()
     * @Assert\Type("bool")
     * @SWG\Property(description="Is house proud", default=false)
     */
    private $houseProud = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_cook", type="boolean", options={"default": false})
     * @JMS\Expose()
     * @Assert\Type("bool")
     * @SWG\Property(description="Is cook", default=false)
     */
    private $cook = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_job", type="boolean", options={"default": false})
     * @JMS\Expose()
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
     * @Assert\Choice(choices={"student", "worker", "unknown"}, strict=true)
     * @SWG\Property(description="The social status",
     *   enum={ "student", "worker", "unknown" }, default="unknown")
     */
    private $socialStatus = ProfileConstants::SOCIAL_UNKNOWN;


    public function __toString() {
        return sprintf(
            "Profile [id: %d, gender: '%s', phoneNumber: '%s', smoker: '%d', houseProud: '%s', cook: '%s', hasJob: %d, diet: '%s', maritalStatus: '%s', socialStatus: '%s']",
            $this->id, $this->gender, $this->phoneNumber, $this->smoker, $this->houseProud, $this->cook, $this->hasJob,
            $this->diet, $this->maritalStatus, $this->socialStatus);
    }


    public function getId() {
        return $this->id;
    }


    public function setGender($gender) {
        $this->gender = $gender;

        return $this;
    }


    public function getGender() {
        return $this->gender;
    }


    public function setPhoneNumber($phoneNumber) {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }


    public function getPhoneNumber() {
        return $this->phoneNumber;
    }


    public function isSmoker() {
        return $this->smoker;
    }


    public function setSmoker(bool $smoker) {
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