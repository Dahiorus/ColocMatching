<?php

namespace App\Core\DTO\User;

use App\Core\DTO\AbstractDto;
use App\Core\Entity\User\UserGender;
use App\Core\Entity\User\UserPreference;
use App\Core\Entity\User\UserType;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("ALL")
 *
 * @author Dahiorus
 */
class UserPreferenceDto extends AbstractDto
{
    /**
     * User type filter
     * @var string
     *
     * @Serializer\Expose
     * @Assert\Choice(choices={ UserType::SEARCH, UserType::PROPOSAL }, strict=true)
     * @SWG\Property(property="type", type="string", enum={ "search", "proposal" }, example="search")
     */
    private $type;

    /**
     * User gender filter
     * @var string
     *
     * @Serializer\Expose
     * @Assert\Choice(choices={ UserGender::MALE, UserGender::FEMALE, UserGender::UNKNOWN }, strict=true)
     * @SWG\Property(property="gender", type="string", enum={ "male", "female" }, example="female")
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
     * @SWG\Property(property="ageStart", type="integer", example="20")
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
     * @SWG\Property(property="ageEnd", type="integer", example="25")
     */
    private $ageEnd;

    /**
     * Only with description
     * @var boolean
     *
     * @Serializer\SerializedName("withDescription")
     * @Assert\Type("boolean")
     * @Serializer\Expose
     * @SWG\Property(property="withDescription", type="boolean", default=false)
     */
    private $withDescription = false;


    public function __toString() : string
    {
        return parent::__toString() . "[type = " . $this->type . ", gender = " . $this->gender
            . ", ageStart = " . $this->ageStart . ", ageEnd = " . $this->ageEnd
            . ", withDescription = " . $this->withDescription . "]";
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


    public function getEntityClass() : string
    {
        return UserPreference::class;
    }

}
