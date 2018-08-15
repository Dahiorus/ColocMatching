<?php

namespace App\Tests\Core\Manager\User;

use App\Core\DTO\User\ProfilePictureDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\Address;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Group\Group;
use App\Core\Entity\User\ProfileConstants;
use App\Core\Entity\User\User;
use App\Core\Entity\User\UserConstants;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Manager\User\UserDtoManager;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Mapper\User\UserDtoMapper;
use App\Tests\Core\Manager\AbstractManagerTest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Unit tests for UserManager
 *
 * @author Dahiorus
 */
class UserDtoManagerTest extends AbstractManagerTest
{
    /** @var UserDtoManagerInterface */
    protected $manager;

    /** @var UserDtoMapper */
    protected $dtoMapper;

    /** @var UserDto $dto */
    protected $testDto;

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;


    protected function initManager()
    {
        $this->dtoMapper = $this->getService("coloc_matching.core.user_dto_mapper");
        $this->passwordEncoder = $this->getService("security.password_encoder");
        $entityValidator = $this->getService("coloc_matching.core.form_validator");
        $pictureDtoMapper = $this->getService("coloc_matching.core.profile_picture_dto_mapper");
        $profileDtoMapper = $this->getService("coloc_matching.core.profile_dto_mapper");
        $announcementPreferenceDtoMapper = $this->getService("coloc_matching.core.announcement_preference_dto_mapper");
        $userPreferenceDtoMapper = $this->getService("coloc_matching.core.user_preference_dto_mapper");
        $userStatusHandler = $this->getService("coloc_matching.core.user_status_handler");

        return new UserDtoManager($this->logger, $this->em, $this->dtoMapper, $entityValidator,
            $pictureDtoMapper, $profileDtoMapper, $announcementPreferenceDtoMapper, $userPreferenceDtoMapper,
            $userStatusHandler);
    }


    protected function initTestData() : array
    {
        return array (
            "email" => "user@yopmail.com",
            "firstName" => "John",
            "lastName" => "Smith",
            "plainPassword" => "secret1234",
            "type" => UserConstants::TYPE_SEARCH);
    }


    protected function cleanData() : void
    {
        $this->manager->deleteAll();
    }


    /**
     * @return UserDto
     * @throws \Exception
     */
    protected function createAndAssertEntity()
    {
        /** @var UserDto $dto */
        $dto = $this->manager->create($this->testData);

        $this->assertDto($dto);

        return $dto;
    }


    /**
     * @param UserDto $dto
     */
    protected function assertDto($dto) : void
    {
        parent::assertDto($dto);
        self::assertNotEmpty($dto->getEmail(), "Expected user to have an email");
        self::assertNotEmpty($dto->getFirstName(), "Expected user to have a first name");
        self::assertNotEmpty($dto->getLastName(), "Expected user to have a last name");
        self::assertNotEmpty($dto->getType(), "Expected user to have a type");
    }


    private function assertProfilePictureDto(ProfilePictureDto $dto)
    {
        parent::assertDto($dto);
        self::assertNotEmpty($dto->getWebPath(), "Expected profile picture to have a web path");
    }


    public function testCreateWithInvalidDataShouldThrowValidationErrors()
    {
        $this->testData["firstName"] = "";
        $this->testData["plainPassword"] = null;

        self::assertValidationError(function () {
            return $this->manager->create($this->testData);
        }, "plainPassword", "firstName");
    }


    public function testCreateWithSameEmailShouldThrowValidationError()
    {
        self::assertValidationError(function () {
            return $this->manager->create($this->testData);
        }, "email");
    }


    /**
     * @throws \Exception
     */
    public function testFindByUsername()
    {
        $user = $this->manager->findByUsername($this->testDto->getEmail());

        $this->assertDto($user);
    }


    /**
     * @throws \Exception
     */
    public function testFindByNonExistingUsername()
    {
        $this->expectException(EntityNotFoundException::class);
        $this->manager->findByUsername("unknown@test.fr");
    }


    /**
     * @throws \Exception
     */
    public function testUpdate()
    {
        $this->testData["type"] = UserConstants::TYPE_PROPOSAL;
        unset($this->testData["plainPassword"]);

        /** @var UserDto $user */
        $user = $this->manager->update($this->testDto, $this->testData, true);

        $this->assertDto($user);
        self::assertEquals(UserConstants::TYPE_PROPOSAL, $user->getType(),
            "Expected user to have type" . UserConstants::TYPE_PROPOSAL);
    }


    /**
     * @throws \Exception
     */
    public function testUpdateWithPassword()
    {
        $data = array ("plainPassword" => "new_password");

        /** @var UserDto $user */
        $user = $this->manager->update($this->testDto, $data, false);

        $this->assertDto($user);

        $userEntity = $this->dtoMapper->toEntity($user);
        self::assertTrue($this->passwordEncoder->isPasswordValid($userEntity, $data["plainPassword"]),
            "Expected user password to be updated");
    }


    /**
     * @throws \Exception
     */
    public function testUpdateWithMissingDataShouldThrowValidationError()
    {
        $this->testData["type"] = null;
        $this->testData["firstName"] = "";
        unset($this->testData["plainPassword"]);

        self::assertValidationError(function () {
            $this->manager->update($this->testDto, $this->testData, true);
        }, "type", "firstName");
    }


    /**
     * @throws \Exception
     */
    public function testUpdatePassword()
    {
        $oldPassword = $this->testData["plainPassword"];
        $data = array ("oldPassword" => $oldPassword, "newPassword" => "new_password");

        $updatedUser = $this->manager->updatePassword($this->testDto, $data);
        $userEntity = $this->dtoMapper->toEntity($updatedUser);

        $this->assertDto($updatedUser);
        self::assertFalse($this->passwordEncoder->isPasswordValid($userEntity, $oldPassword),
            "Expected user password to be updated");
        self::assertTrue($this->passwordEncoder->isPasswordValid($userEntity, $data["newPassword"]),
            "Expected user password to be valid");
    }


    public function testUpdatePasswordWithInvalidOldPasswordShouldThrowValidationError()
    {
        $oldPassword = "wrong_password";
        $data = array ("oldPassword" => $oldPassword, "newPassword" => "new_password");

        self::assertValidationError(function () use ($data) {
            $this->manager->updatePassword($this->testDto, $data);
        }, "oldPassword");
    }


    /**
     * @throws \Exception
     */
    public function testBanUser()
    {
        $bannedUser = $this->manager->updateStatus($this->testDto, UserConstants::STATUS_BANNED);

        $this->assertDto($bannedUser);
        self::assertEquals($bannedUser->getStatus(), UserConstants::STATUS_BANNED, "Expected user to be banned");
    }


    /**
     * @throws \Exception
     */
    public function testBanUserWithAnnouncement()
    {
        $this->testDto = $this->manager->update($this->testDto, array ("type" => UserConstants::TYPE_PROPOSAL), false);

        /** @var User $creator */
        $creator = $this->em->getRepository($this->testDto->getEntityClass())->find($this->testDto->getId());
        $announcement = new Announcement($creator);
        $announcement->setType(Announcement::TYPE_RENT);
        $announcement->setTitle("announcement to delete with user");
        $announcement->setRentPrice(500);
        $announcement->setStartDate(new \DateTime());
        $announcement->setLocation(new Address());

        $this->em->persist($announcement);
        $this->em->flush();
        $this->testDto->setAnnouncementId($announcement->getId());

        $bannedUser = $this->manager->updateStatus($this->testDto, UserConstants::STATUS_BANNED);

        $this->assertDto($bannedUser);
        self::assertEquals($bannedUser->getStatus(), UserConstants::STATUS_BANNED, "Expected user to be banned");

        $announcement = $this->em->find(Announcement::class, $this->testDto->getAnnouncementId());
        self::assertNull($announcement);
    }


    /**
     * @throws \Exception
     */
    public function testBanUserWithGroup()
    {
        $this->testDto = $this->manager->update($this->testDto, array ("type" => UserConstants::TYPE_SEARCH), false);

        /** @var User $creator */
        $creator = $this->em->find($this->testDto->getEntityClass(), $this->testDto->getId());
        $group = new Group($creator);
        $group->setName("group test");

        $this->em->persist($group);
        $this->em->flush();
        $this->testDto->setGroupId($group->getId());

        $bannedUser = $this->manager->updateStatus($this->testDto, UserConstants::STATUS_BANNED);

        $this->assertDto($bannedUser);
        self::assertEquals($bannedUser->getStatus(), UserConstants::STATUS_BANNED, "Expected user to be banned");

        $group = $this->em->find(Group::class, $this->testDto->getGroupId());
        self::assertNull($group);
    }


    /**
     * @throws \Exception
     */
    public function testEnableUser()
    {
        $bannedUser = $this->manager->updateStatus($this->testDto, UserConstants::STATUS_ENABLED);

        $this->assertDto($bannedUser);
        self::assertEquals($bannedUser->getStatus(), UserConstants::STATUS_ENABLED, "Expected user to be enabled");
    }


    /**
     * @throws \Exception
     */
    public function testDisableUser()
    {
        $bannedUser = $this->manager->updateStatus($this->testDto, UserConstants::STATUS_VACATION);

        $this->assertDto($bannedUser);
        self::assertEquals($bannedUser->getStatus(), UserConstants::STATUS_VACATION, "Expected user to be disabled");
    }


    /**
     * @throws \Exception
     */
    public function testUpdateUserWithUnknownStatusShouldThrowInvalidParameter()
    {
        $this->expectException(InvalidParameterException::class);

        $this->manager->updateStatus($this->testDto, "unknown");
    }


    /**
     * @throws \Exception
     */
    public function testUpdateUserStatusWithCurrentStatus()
    {
        $status = $this->testDto->getStatus();
        $userDto = $this->manager->updateStatus($this->testDto, $status);

        $this->assertDto($userDto);
        self::assertEquals($userDto->getStatus(), $status, "Expected user to have status '$status'");
    }


    /**
     * @throws \Exception
     */
    public function testUploadProfilePicture()
    {
        $path = dirname(__FILE__) . "/../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "user-img.jpg");

        $picture = $this->manager->uploadProfilePicture($this->testDto, $file);

        $this->assertProfilePictureDto($picture);
    }


    public function testUploadTextFileAsPictureShouldThrowValidationError()
    {
        $path = dirname(__FILE__) . "/../../Resources/file.txt";
        $file = new UploadedFile($path, "file.txt", "text/plain", null, null, true);

        self::assertValidationError(function () use ($file) {
            $this->manager->uploadProfilePicture($this->testDto, $file);
        }, "file");
    }


    /**
     * @throws \Exception
     */
    public function testDeleteProfilePicture()
    {
        $path = dirname(__FILE__) . "/../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "user-img.jpg");

        $picture = $this->manager->uploadProfilePicture($this->testDto, $file);
        $this->assertProfilePictureDto($picture);
        $this->testDto->setPicture($picture);

        $this->manager->deleteProfilePicture($this->testDto);

        /** @var UserDto $user */
        $user = $this->manager->read($this->testDto->getId());
        self::assertEmpty($user->getPicture());
    }


    public function testDeleteNonExistingProfilePicture()
    {
        self::assertEmpty($this->testDto->getPicture());

        $this->manager->deleteProfilePicture($this->testDto);

        self::assertEmpty($this->testDto->getPicture());
    }


    /**
     * @throws \Exception
     */
    public function testGetProfile()
    {
        $profile = $this->manager->getProfile($this->testDto);

        parent::assertDto($profile);
    }


    /**
     * @throws \Exception
     */
    public function testUpdateProfile()
    {
        $data = array (
            "gender" => ProfileConstants::GENDER_MALE,
            "description" => "This is a description.",
            "hasJob" => true,
            "diet" => ProfileConstants::DIET_VEGAN
        );

        $updatedProfile = $this->manager->updateProfile($this->testDto, $data, true);

        parent::assertDto($updatedProfile);
        self::assertEquals($data["description"], $updatedProfile->getDescription(),
            "Expected profile description to be updated");
        self::assertEquals($data["gender"], $updatedProfile->getGender(), "Expected profile gender to be updated");
        self::assertEquals($data["hasJob"], $updatedProfile->hasJob(), "Expected profile 'hasJob' to be updated");
        self::assertEquals($data["diet"], $updatedProfile->getDiet(), "Expected profile diet to be updated");
    }


    public function testUpdateProfileWithInvalidDataShouldThrowValidationError()
    {
        $data = array (
            "gender" => "x",
            "description" => "This is a description.",
            "hasJob" => true,
            "diet" => "wrong value"
        );

        self::assertValidationError(function () use ($data) {
            $this->manager->updateProfile($this->testDto, $data, true);
        }, "gender", "diet");
    }


    public function testGetAnnouncementPreference()
    {
        $preference = $this->manager->getAnnouncementPreference($this->testDto);

        parent::assertDto($preference);
    }


    /**
     * @throws \Exception
     */
    public function testUpdateAnnouncementPreference()
    {
        $data = array (
            "address" => "Paris 75012",
            "startDateAfter" => "2018-09-01",
            "startDateBefore" => "2018-09-30"
        );

        $updatedPreference = $this->manager->updateAnnouncementPreference($this->testDto, $data, true);

        parent::assertDto($updatedPreference);
        self::assertContains($data["address"], $updatedPreference->getAddress());
        self::assertEquals($data["startDateAfter"], $updatedPreference->getStartDateAfter()->format("Y-m-d"));
        self::assertEquals($data["startDateBefore"], $updatedPreference->getStartDateBefore()->format("Y-m-d"));
    }


    public function testUpdateAnnouncementPreferenceWithInvalidDataShouldThrowValidationError()
    {
        $data = array (
            "address" => "Paris 75012",
            "startDateAfter" => "2018-09-01",
            "startDateBefore" => "2018-09-30",
            "rentPriceStart" => -1
        );

        self::assertValidationError(function () use ($data) {
            $this->manager->updateAnnouncementPreference($this->testDto, $data, true);
        }, "rentPriceStart");
    }


    public function testGetUserPreference()
    {
        $preference = $this->manager->getUserPreference($this->testDto);

        parent::assertDto($preference);
    }


    /**
     * @throws \Exception
     */
    public function testUpdateUserPreference()
    {
        $data = array (
            "type" => UserConstants::TYPE_PROPOSAL,
            "gender" => ProfileConstants::GENDER_MALE,
            "withDescription" => true
        );

        $updatedPreference = $this->manager->updateUserPreference($this->testDto, $data, true);

        parent::assertDto($updatedPreference);
        self::assertContains($data["type"], $updatedPreference->getType());
        self::assertEquals($data["gender"], $updatedPreference->getGender());
        self::assertEquals($data["withDescription"], $updatedPreference->withDescription());
    }


    public function testUpdateUserPreferenceWithInvalidDataShouldThrowValidationError()
    {
        $data = array (
            "type" => "wrong_value",
            "gender" => ProfileConstants::GENDER_MALE,
            "hasJob" => false,
            "maritalStatus" => "wrong_value"
        );

        self::assertValidationError(function () use ($data) {
            $this->manager->updateUserPreference($this->testDto, $data, true);
        }, "type", "maritalStatus");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function addRole()
    {
        $role = "ROLE_TEST";

        $this->testDto = $this->manager->addRole($this->testDto, $role);
        /** @var User $entity */
        $entity = $this->em->find(User::class, $this->testDto->getId());

        self::assertContains($role, $entity->getRoles(), "Expected user to have '$role'");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function updateUserWithAdminRole()
    {
        $this->testDto->setRoles(array ("ROLE_USER", "ROLE_SUPER_ADMIN"));

        $this->testDto = $this->manager->update($this->testDto, $this->testData, true);

        self::assertNotEmpty($this->testDto->getRoles(), "Expected the user to have roles");
        self::assertContains("ROLE_SUPER_ADMIN", $this->testDto->getRoles(),
            "Expected the user to have the role 'ROLE_SUPER_ADMIN'");
    }
}
