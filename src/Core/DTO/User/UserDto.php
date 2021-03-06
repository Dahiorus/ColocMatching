<?php

namespace App\Core\DTO\User;

use App\Core\DTO\AbstractDto;
use App\Core\DTO\Visit\VisitableDto;
use App\Core\Entity\User\User;
use App\Core\Entity\User\UserStatus;
use App\Core\Service\VisitorInterface;
use App\Core\Validator\Constraint\UniqueValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("ALL")
 * @UniqueValue(properties="email", groups={"Create"})
 * @Hateoas\Relation(
 *   name="self",
 *   href= @Hateoas\Route(name="rest_get_user", absolute=true, parameters={ "id" = "expr(object.getId())" })
 * )
 * @Hateoas\Relation(
 *   name="announcements",
 *   href= @Hateoas\Route(
 *     name="rest_get_user_announcements", absolute=true, parameters={ "id" = "expr(object.getId())" }),
 *   exclusion= @Hateoas\Exclusion(excludeIf="expr(not object.hasAnnouncements())")
 * )
 * @Hateoas\Relation(
 *   name="groups",
 *   href= @Hateoas\Route(
 *     name="rest_get_user_groups", absolute=true, parameters={ "id" = "expr(object.getId())" }),
 *   exclusion= @Hateoas\Exclusion(excludeIf="expr(not object.hasGroups())")
 * )
 * @Hateoas\Relation(
 *   name="picture",
 *   embedded= @Hateoas\Embedded(content="expr(object.getPicture())")
 * )
 * @Hateoas\Relation(
 *   name="userPreference",
 *   href= @Hateoas\Route(
 *     name="rest_get_user_user_preference", absolute=true, parameters={ "id" = "expr(object.getId())" }),
 *   exclusion= @Hateoas\Exclusion(excludeIf="expr(not is_granted(['ROLE_USER']))")
 * )
 * @Hateoas\Relation(
 *   name="announcementPreference",
 *   href= @Hateoas\Route(
 *     name="rest_get_user_announcement_preference", absolute=true, parameters={ "id" = "expr(object.getId())" }),
 *   exclusion= @Hateoas\Exclusion(excludeIf="expr(not is_granted(['ROLE_USER']))")
 * )
 * @Hateoas\Relation(
 *   name="invitations",
 *   href= @Hateoas\Route(
 *     name="rest_get_user_invitations", absolute=true, parameters={ "id" = "expr(object.getId())" }),
 *   exclusion= @Hateoas\Exclusion(excludeIf="expr(not is_granted(['ROLE_USER']))")
 * )
 * @Hateoas\Relation(
 *   name="visits",
 *   href= @Hateoas\Route(
 *     name="rest_get_user_visits", absolute=true, parameters={ "id" = "expr(object.getId())" }),
 *   exclusion= @Hateoas\Exclusion(excludeIf="expr(not is_granted(['ROLE_USER']))")
 * )
 */
class UserDto extends AbstractDto implements UserInterface, VisitableDto
{
    /**
     * User email
     *
     * @var string
     *
     * @Serializer\Expose
     * @Assert\NotBlank
     * @Assert\Email(mode="strict")
     * @SWG\Property(property="email", type="string", format="email", example="user@test.com")
     */
    private $email;

    /**
     * User raw password (not persisted)
     *
     * @var string
     *
     * @Assert\NotBlank(groups={ "Create" })
     * @Assert\Length(min=8, max=4096)
     */
    private $plainPassword;

    /**
     * User password
     *
     * @var string
     */
    private $password;

    /**
     * User status
     *
     * @var string
     *
     * @Serializer\Expose
     * @SWG\Property(
     *   property="status", type="string", enum={"pending", "enabled", "vacation", "banned"}, default="pending",
     *   readOnly=true)
     */
    private $status = UserStatus::PENDING;

    /**
     * User roles
     *
     * @var string[]
     */
    private $roles = [];

    /**
     * User first name
     *
     * @var string
     *
     * @Serializer\Expose
     * @Serializer\SerializedName("firstName")
     * @Assert\NotBlank
     * @SWG\Property(property="firstName", type="string", example="John")
     */
    private $firstName;

    /**
     * User last name
     *
     * @var string
     *
     * @Serializer\Expose
     * @Serializer\SerializedName("lastName")
     * @Assert\NotBlank
     * @SWG\Property(property="lastName", type="string", example="Smith")
     */
    private $lastName;

    /**
     * User type
     *
     * @var string
     *
     * @Serializer\Expose
     * @Assert\NotBlank(groups={ "Register", "Self" })
     * @Assert\Choice(choices={"search", "proposal"}, strict=true)
     * @SWG\Property(property="type", type="string", example="search")
     */
    private $type;

    /**
     * User gender
     *
     * @var string
     *
     * @Serializer\Expose
     * @Assert\Choice(choices={"male", "female"}, strict=true)
     * @SWG\Property(property="gender", type="string")
     */
    private $gender;

    /**
     * User birth date
     *
     * @var \DateTime
     *
     * @Serializer\Expose
     * @Serializer\SerializedName("birthDate")
     * @SWG\Property(property="birthDate", type="string", format="date", example="1990-01-01")
     */
    private $birthDate;

    /**
     * User description
     *
     * @var string
     *
     * @Serializer\Expose
     * @SWG\Property(property="description", type="string")
     */
    private $description;

    /**
     * User phone number
     *
     * @var string
     *
     * @Serializer\Expose
     * @Serializer\SerializedName("phoneNumber")
     * @SWG\Property(property="phoneNumber", type="string")
     */
    private $phoneNumber;

    /**
     * User tags
     *
     * @var Collection<string>
     *
     * @Serializer\Expose
     * @SWG\Property(property="tags", type="array", @SWG\Items(type="string"))
     */
    private $tags;

    /**
     * Last login date time
     *
     * @var \DateTime
     *
     * @Serializer\Expose
     * @Serializer\SerializedName("lastLogin")
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:s'>")
     * @SWG\Property(readOnly=true)
     */
    private $lastLogin;

    /**
     * If the user has announcements
     *
     * @var bool
     */
    private $hasAnnouncements;

    /**
     * If the user has groups
     *
     * @var bool
     */
    private $hasGroups;

    /**
     * User's profile
     *
     * @var integer
     */
    private $profileId;

    /**
     * User's users preference
     *
     * @var integer
     */
    private $userPreferenceId;

    /**
     * User's announcements preference
     *
     * @var integer
     */
    private $announcementPreferenceId;

    /**
     * User's profile picture
     *
     * @var ProfilePictureDto
     */
    private $picture;


    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }


    public function __toString() : string
    {
        $lastLogin = empty($this->lastLogin) ? null : $this->lastLogin->format(\DateTime::ISO8601);

        return parent::__toString()
            . "[email = '" . $this->email
            . "', status = '" . $this->status
            . "', firstName = '" . $this->firstName
            . "', lastName = '" . $this->lastName
            . "', type = '" . $this->type
            . "', lastLogin = " . $lastLogin
            . ", hasAnnouncements = " . $this->hasAnnouncements
            . ", hasGroups = " . $this->hasGroups
            . "]";
    }


    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }


    public function getSalt()
    {
        return null; // nothing to do
    }


    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }


    /**
     * @param null|string $email
     *
     * @return UserDto
     */
    public function setEmail(?string $email) : UserDto
    {
        $this->email = $email;

        return $this;
    }


    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }


    /**
     * @param null|string $plainPassword
     *
     * @return UserDto
     */
    public function setPlainPassword(?string $plainPassword) : UserDto
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }


    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }


    /**
     * @param null|string $password
     */
    public function setPassword(?string $password) : void
    {
        $this->password = $password;
    }


    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @param null|string $status
     *
     * @return UserDto
     */
    public function setStatus(?string $status) : UserDto
    {
        $this->status = $status;

        return $this;
    }


    /**
     * @return string[]
     */
    public function getRoles()
    {
        return $this->roles;
    }


    /**
     * @param string[] $roles
     *
     * @return UserDto
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }


    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }


    /**
     * @param null|string $firstName
     *
     * @return UserDto
     */
    public function setFirstName(?string $firstName) : UserDto
    {
        $this->firstName = $firstName;

        return $this;
    }


    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }


    /**
     * @param null|string $lastName
     *
     * @return UserDto
     */
    public function setLastName(?string $lastName) : UserDto
    {
        $this->lastName = $lastName;

        return $this;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @param null|string $type
     *
     * @return UserDto
     */
    public function setType(?string $type) : UserDto
    {
        $this->type = $type;

        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }


    /**
     * @param \DateTime|null $lastLogin
     *
     * @return UserDto
     */
    public function setLastLogin(\DateTime $lastLogin = null) : UserDto
    {
        $this->lastLogin = $lastLogin;

        return $this;
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
            try
            {
                return $this->birthDate->diff(new \DateTime())->y;
            }
            catch (\Exception $e)
            {
                // do nothing, return 0
            }
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


    public function getTags()
    {
        return $this->tags;
    }


    public function setTags(Collection $tags = null)
    {
        $this->tags = $tags;

        return $this;
    }


    public function hasAnnouncements()
    {
        return $this->hasAnnouncements;
    }


    public function setHasAnnouncements(?bool $hasAnnouncements)
    {
        $this->hasAnnouncements = $hasAnnouncements;

        return $this;
    }


    public function hasGroups()
    {
        return $this->hasGroups;
    }


    public function setHasGroups(?bool $hasGroups)
    {
        $this->hasGroups = $hasGroups;

        return $this;
    }


    /**
     * @return int
     */
    public function getProfileId()
    {
        return $this->profileId;
    }


    /**
     * @param int $profileId
     *
     * @return UserDto
     */
    public function setProfileId(?int $profileId) : UserDto
    {
        $this->profileId = $profileId;

        return $this;
    }


    /**
     * @return int
     */
    public function getUserPreferenceId()
    {
        return $this->userPreferenceId;
    }


    /**
     * @param int $userPreferenceId
     *
     * @return UserDto
     */
    public function setUserPreferenceId(?int $userPreferenceId) : UserDto
    {
        $this->userPreferenceId = $userPreferenceId;

        return $this;
    }


    /**
     * @return int|null
     */
    public function getAnnouncementPreferenceId()
    {
        return $this->announcementPreferenceId;
    }


    /**
     * @param int $announcementPreferenceId
     *
     * @return UserDto
     */
    public function setAnnouncementPreferenceId(?int $announcementPreferenceId) : UserDto
    {
        $this->announcementPreferenceId = $announcementPreferenceId;

        return $this;
    }


    /**
     * @return ProfilePictureDto|null
     */
    public function getPicture()
    {
        return $this->picture;
    }


    /**
     * @param ProfilePictureDto $picture
     *
     * @return UserDto
     */
    public function setPicture(ProfilePictureDto $picture = null) : UserDto
    {
        $this->picture = $picture;

        return $this;
    }


    public function getDisplayName() : string
    {
        return $this->firstName . " " . $this->lastName;
    }


    public function getUsername() : string
    {
        return $this->email;
    }


    public function accept(VisitorInterface $visitor)
    {
        $visitor->visit($this);
    }


    public function getEntityClass() : string
    {
        return User::class;
    }
}
