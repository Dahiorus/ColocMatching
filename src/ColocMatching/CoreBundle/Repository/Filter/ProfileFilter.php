<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use Swagger\Annotations as SWG;

/**
 * Profile query filter class
 *
 * @SWG\Definition(definition="ProfileFilter")
 *
 * @author brondon.ung
 */
class ProfileFilter {

    /**
     * @var string
     *
     * @SWG\Property(description="Gender filter")
     */
    private $gender;

    /**
     * @var integer
     *
     * @SWG\Property(description="Age start range filter")
     */
    private $ageStart;

    /**
     * @var integer
     *
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
     * @SWG\Property(description="Diet filter")
     */
    private $diet;

    /**
     * @var string
     *
     * @SWG\Property(description="Social status filter")
     */
    private $socialStatus;

    /**
     * @var string
     *
     * @SWG\Property(description="Marital status filter")
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


    public function setGender(?string $gender) {
        $this->gender = $gender;
        return $this;
    }


    public function getAgeStart() {
        return $this->ageStart;
    }


    public function setAgeStart(?int $ageStart) {
        $this->ageStart = $ageStart;
        return $this;
    }


    public function getAgeEnd() {
        return $this->ageEnd;
    }


    public function setAgeEnd(?int $ageEnd) {
        $this->ageEnd = $ageEnd;
        return $this;
    }


    public function getWithDescription() {
        return $this->withDescription;
    }


    public function setWithDescription(bool $withDescription = false) {
        $this->withDescription = $withDescription;
        return $this;
    }


    public function isSmoker() {
        return $this->smoker;
    }


    public function setSmoker(?bool $smoker) {
        $this->smoker = $smoker;
        return $this;
    }


    public function hasJob() {
        return $this->hasJob;
    }


    public function setHasJob(?bool $hasJob) {
        $this->hasJob = $hasJob;
        return $this;
    }


    public function getDiet() {
        return $this->diet;
    }


    public function setDiet(?string $diet) {
        $this->diet = $diet;
        return $this;
    }


    public function getSocialStatus() {
        return $this->socialStatus;
    }


    public function setSocialStatus(?string $socialStatus) {
        $this->socialStatus = $socialStatus;
        return $this;
    }


    public function getMaritalStatus() {
        return $this->maritalStatus;
    }


    public function setMaritalStatus(?string $maritalStatus) {
        $this->maritalStatus = $maritalStatus;
        return $this;
    }

}