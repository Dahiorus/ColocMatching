<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Entity\User\ProfileConstants;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Profile query filter class
 *
 * @SWG\Definition(definition="ProfileFilter")
 * @author brondon.ung
 */
class ProfileFilter {

    /**
     * @var string
     *
     * @Assert\Choice(choices={ProfileConstants::GENDER_MALE, ProfileConstants::GENDER_FEMALE, ProfileConstants::GENDER_UNKNOWN},
     *   strict=true)
     * @SWG\Property(description="Gender filter", enum={ "male", "female", "unknown" })
     */
    private $gender;

    /**
     * @var integer
     *
     * @Assert\GreaterThanOrEqual(payload=18)
     * @Assert\Type("interger")
     * @SWG\Property(description="Age start range filter")
     */
    private $ageStart;

    /**
     * @var integer
     *
     * @Assert\GreaterThanOrEqual(payload=18)
     * @Assert\Type("interger")
     * @SWG\Property(description="Age end range filter")
     */
    private $ageEnd;

    /**
     * @var boolean
     *
     * @SWG\Property(description="Only with description", default=false)
     */
    private $withDescription = false;

    /**
     * @var boolean
     *
     * @SWG\Property(description="Is smoker filter")
     */
    private $smoker;

    /**
     * @var boolean
     *
     * @SWG\Property(description="Has job filter")
     */
    private $hasJob;

    /**
     * @var string
     *
     * @Assert\Choice(choices={ProfileConstants::DIET_MEAT_EATER, ProfileConstants::DIET_VEGETARIAN, ProfileConstants::DIET_VEGAN, ProfileConstants::DIET_UNKNOWN},
     *   strict=true)
     * @SWG\Property(description="Diet filter", enum={ "meat_eater", "vegetarian", "vegan", "unknown" })
     */
    private $diet;

    /**
     * @var string
     *
     * @Assert\Choice(choices={ProfileConstants::SOCIAL_STUDENT, ProfileConstants::SOCIAL_WORKER, ProfileConstants::SOCIAL_UNKNOWN},
     *   strict=true)
     * @SWG\Property(description="Social status filter", enum={ "student", "worker", "unknown" })
     */
    private $socialStatus;

    /**
     * @var string
     *
     * @Assert\Choice(choices={ProfileConstants::MARITAL_COUPLE, ProfileConstants::MARITAL_SINGLE, ProfileConstants::MARITAL_UNKNOWN},
     *   strict=true)
     * @SWG\Property(description="Marital status filter", enum={ "couple", "single", "unknown" })
     */
    private $maritalStatus;


    public function __toString() {
        return sprintf(
            "ProfileFilter [gender: '%s', age: [%d - %d], withDescription: %d, smoker: %d, hasJob: %d, socialStatus: '%s', maritalStatus: '%s']",
            $this->gender, $this->ageStart, $this->ageEnd, $this->withDescription, $this->smoker, $this->hasJob,
            $this->socialStatus, $this->maritalStatus);
    }


    public function getGender() {
        return $this->gender;
    }


    public function setGender(string $gender = null) {
        $this->gender = $gender;
        return $this;
    }


    public function getAgeStart() {
        return $this->ageStart;
    }


    public function setAgeStart(int $ageStart = null) {
        $this->ageStart = $ageStart;
        return $this;
    }


    public function getAgeEnd() {
        return $this->ageEnd;
    }


    public function setAgeEnd(int $ageEnd = null) {
        $this->ageEnd = $ageEnd;
        return $this;
    }


    public function getWithDescription() {
        return $this->withDescription;
    }


    public function setWithDescription(bool $withDescription) {
        $this->withDescription = $withDescription;
        return $this;
    }


    public function isSmoker() {
        return $this->smoker;
    }


    public function setSmoker(bool $smoker) {
        $this->smoker = $smoker;
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


    public function getSocialStatus() {
        return $this->socialStatus;
    }


    public function setSocialStatus(string $socialStatus = null) {
        $this->socialStatus = $socialStatus;
        return $this;
    }


    public function getMaritalStatus() {
        return $this->maritalStatus;
    }


    public function setMaritalStatus(string $maritalStatus = null) {
        $this->maritalStatus = $maritalStatus;
        return $this;
    }

}