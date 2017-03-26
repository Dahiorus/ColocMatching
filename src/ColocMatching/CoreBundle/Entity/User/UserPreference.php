<?php

namespace ColocMatching\CoreBundle\Entity\User;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Entity\User\ProfileConstants;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AnnouncementPreference
 *
 * @ORM\Table(name="user_preference")
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="UserPreference")
 */
class UserPreference implements EntityInterface {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     * @SWG\Property(description="UserPreference id", readOnly=true)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="user_type", type="string", nullable=true)
     * @JMS\Expose()
     * @Assert\Choice(choices={ UserConstants::TYPE_SEARCH, UserConstants::TYPE_PROPOSAL },
     *   strict=true)
     * @SWG\Property(description="User type", enum={ "search", "proposal" })
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", nullable=true)
     * @JMS\Expose()
     * @Assert\Choice(choices={ ProfileConstants::GENDER_MALE, ProfileConstants::GENDER_FEMALE, ProfileConstants::GENDER_UNKNOWN },
     *   strict=true)
     * @SWG\Property(description="Gender filter", enum={ "male", "female", "unknown" })
     */
    private $gender;

    /**
     * @var integer
     *
     * @ORM\Column(name="age_start", type="integer", nullable=true)
     * @JMS\SerializedName("ageStart")
     * @JMS\Expose()
     * @SWG\Property(description="Age start range filter")
     */
    private $ageStart;

    /**
     * @var integer
     *
     * @ORM\Column(name="age_end", type="integer", nullable=true)
     * @JMS\SerializedName("ageEnd")
     * @JMS\Expose()
     * @SWG\Property(description="Age end range filter")
     */
    private $ageEnd;

    /**
     * @var boolean
     *
     * @ORM\Column(name="with_description", type="boolean", nullable=true)
     * @JMS\SerializedName("withDescription")
     * @JMS\Expose()
     * @SWG\Property(description="Only with description", default=false)
     */
    private $withDescription = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_smoker", type="boolean", nullable=true)
     * @JMS\SerializedName("smoker")
     * @JMS\Expose()
     * @SWG\Property(description="Is smoker filter")
     */
    private $smoker;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_job", type="boolean", nullable=true)
     * @JMS\SerializedName("hasJob")
     * @JMS\Expose()
     * @SWG\Property(description="Has job filter")
     */
    private $hasJob;

    /**
     * @var string
     *
     * @ORM\Column(name="diet", type="string", nullable=true)
     * @JMS\Expose()
     * @Assert\Choice(choices={ ProfileConstants::DIET_MEAT_EATER, ProfileConstants::DIET_VEGETARIAN, ProfileConstants::DIET_VEGAN, ProfileConstants::DIET_UNKNOWN },
     *   strict=true)
     * @SWG\Property(description="Diet filter", enum={ "meat_eater", "vegetarian", "vegan", "unknown" })
     */
    private $diet;

    /**
     * @var string
     *
     * @ORM\Column(name="social_status", type="string", nullable=true)
     * @JMS\SerializedName("socialStatus")
     * @JMS\Expose()
     * @Assert\Choice(choices={ProfileConstants::SOCIAL_STUDENT, ProfileConstants::SOCIAL_WORKER, ProfileConstants::SOCIAL_UNKNOWN},
     *   strict=true)
     * @SWG\Property(description="Social status filter", enum={ "student", "worker", "unknown" })
     */
    private $socialStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="marital_status", type="string", nullable=true)
     * @JMS\SerializedName("maritalStatus")
     * @JMS\Expose()
     * @Assert\Choice(choices={ ProfileConstants::MARITAL_COUPLE, ProfileConstants::MARITAL_SINGLE, ProfileConstants::MARITAL_UNKNOWN })
     * @SWG\Property(description="Marital status filter", enum={ "couple", "single", "unknown" })
     */
    private $maritalStatus;


    public function __toString(): string {
        return sprintf(
            "UserPreference [id: %d, type: '%s', gender: %s, ageStart: %d, ageEnd: %d, withDescription: %d, smoker: %d, hasJob: %d, diet: '%s', socialStatus: '%s', maritalStatus: '%s']",
            $this->id, $this->type, $this->gender, $this->ageStart, $this->ageEnd, $this->withDescription, $this->smoker,
            $this->hasJob, $this->diet, $this->socialStatus, $this->maritalStatus);
    }


    public function getType() {
        return $this->type;
    }


    public function setType(string $type = null) {
        $this->type = $type;
        return $this;
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