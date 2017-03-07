<?php

namespace ColocMatching\CoreBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Profile
 *
 * @ORM\Table(name="profile")
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Profile")
 */
class Profile {

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
     *   enum={ "male", "female", "unknown" }, default="unknown"
     * )
     */
    private $gender = UserConstants::GENDER_UNKNOWN;

    /**
     * @var string
     *
     * @ORM\Column(name="phonenumber", type="string", length=10, nullable=true)
     * @JMS\Expose()
     * @JMS\SerializedName("phoneNumber")
     * @SWG\Property(description="The phone number")
     */
    private $phoneNumber;


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

}