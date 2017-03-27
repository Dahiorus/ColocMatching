<?php

namespace ColocMatching\CoreBundle\Tests\Manager\User;

use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Entity\User\ProfileConstants;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use ColocMatching\CoreBundle\Tests\TestCase;
use ColocMatching\CoreBundle\Repository\Filter\ProfileFilter;

/**
 * Unit tests for UserManager
 *
 * @author brondon.ung
 */
class UserManagerTest extends TestCase {

    private $userManager;


    protected function setUp() {
        $this->userManager = self::getContainer()->get("coloc_matching.core.user_manager");
    }


    protected function tearDown() {
    }


    public function testCreateUser() {
        self::$logger->info("Test creating a user");

        $data = array (
            "email" => "user@phpunit.fr",
            "plainPassword" => "password",
            "firstname" => "User",
            "lastname" => "Test");
        $user = $this->userManager->create($data);

        $this->assertNotNull($user);
        $this->assertEquals("user@phpunit.fr", $user->getEmail());
        $this->assertEquals("User", $user->getFirstname());
        $this->assertEquals("Test", $user->getLastname());
    }


    public function testCreateUserWithFailure() {
        self::$logger->info("Test creating a user with failure");

        $this->expectException(InvalidFormDataException::class);

        $data = array ("email" => "user-fail@phpunit.fr");
        $this->userManager->create($data);
    }


    public function testListUsers() {
        self::$logger->info("Test listing users");

        $users = $this->userManager->list(new UserFilter());

        $this->assertNotNull($users);

        foreach ($users as $user) {
            $this->assertInstanceOf(User::class, $user);
        }
    }


    public function testReadUser() {
        self::$logger->info("Test reading user");

        $user = $this->userManager->read(1);

        $this->assertNotNull($user);
        $this->assertEquals(1, $user->getId());
    }


    public function testReadUserWithFailure() {
        self::$logger->info("Test reading user with failure");

        $this->expectException(UserNotFoundException::class);

        $this->userManager->read(999);
    }


    public function testFindUserByUsername() {
        self::$logger->info("Test finding user by username");

        $user = $this->userManager->findByUsername("user@phpunit.fr");

        $this->assertNotNull($user);
        $this->assertEquals("user@phpunit.fr", $user->getUsername());
    }


    public function testFindUserByUsernameWithFailure() {
        self::$logger->info("Test finding user by username with failure");

        $this->expectException(UserNotFoundException::class);

        $this->userManager->findByUsername("user-fail@phpunit.fr");
    }


    public function testUpdateUser() {
        self::$logger->info("Test updating user");

        $user = $this->userManager->findByUsername("user@phpunit.fr");
        $this->assertNotNull($user);

        $userData = $this->userToArray($user);
        $userData["plainPassword"] = "php-unit";
        $userData["lastname"] = "Php Unit";

        $updatedUser = $this->userManager->update($user, $userData);

        $this->assertEquals($updatedUser->getId(), $user->getId());
        $this->assertEquals("Php Unit", $updatedUser->getLastname());
        $this->assertEquals($user->getEmail(), $updatedUser->getEmail());
    }


    public function testPartialUpdateUser() {
        self::$logger->info("Test partial updating user");

        $user = $this->userManager->findByUsername("user@phpunit.fr");
        $this->assertNotNull($user);

        $userData = [ "firstname" => "Titi"];
        $updatedUser = $this->userManager->partialUpdate($user, $userData);

        $this->assertEquals("Titi", $updatedUser->getFirstname());
    }


    public function testDeleteUser() {
        self::$logger->info("Test deleting user");

        $data = array (
            "email" => "user2@phpunit.fr",
            "plainPassword" => "password",
            "firstname" => "User",
            "lastname" => "Test");
        $user = $this->userManager->create($data);
        $this->assertNotNull($user);

        $this->userManager->delete($user);

        $this->expectException(UserNotFoundException::class);
        $this->userManager->findByUsername($data["email"]);
    }


    public function testUploadProfilePicture() {
        self::$logger->info("Test uploading a profile picture for a user");

        $user = $this->userManager->findByUsername("user@phpunit.fr");
        $this->assertNotNull($user);

        $file = $this->createTempFile(dirname(__FILE__) . "/../../Resources/uploads/image.jpg", "profile-img.jpg");
        $updatedPicture = $this->userManager->uploadProfilePicture($user, $file);

        $this->assertNotNull($updatedPicture);
    }


    public function testDeleteProfilePicture() {
        self::$logger->info("Test deleting a profile picture of a user");

        $user = $this->userManager->findByUsername("user@phpunit.fr");
        $this->assertNotNull($user);

        $this->userManager->deleteProfilePicture($user);
        $this->assertNull($user->getPicture());
    }


    public function testUpdateProfile() {
        self::$logger->info("Test updating a profile of a User");

        $user = $this->userManager->findByUsername("user@phpunit.fr");
        $this->assertNotNull($user);

        $profileData = $this->profileToArray($user->getProfile());
        $profileData["gender"] = ProfileConstants::GENDER_MALE;
        $profileData["diet"] = ProfileConstants::DIET_MEAT_EATER;
        $profileData["hasJob"] = true;

        $updatedProfile = $this->userManager->updateProfile($user, $profileData);

        $this->assertEquals($user->getProfile()->getId(), $updatedProfile->getId());
        $this->assertEquals($profileData["gender"], $updatedProfile->getGender());
        $this->assertEquals($profileData["diet"], $updatedProfile->getDiet());
        $this->assertEquals($profileData["hasJob"], $updatedProfile->hasJob());
    }


    public function testPartialUpdateProfile() {
        self::$logger->info("Test partial updating a profile of a User");

        $user = $this->userManager->findByUsername("user@phpunit.fr");
        $this->assertNotNull($user);

        $profileData = array ("maritalStatus" => ProfileConstants::MARITAL_SINGLE, "hasJob" => false);
        $updatedProfile = $this->userManager->partialUpdateProfile($user, $profileData);

        $this->assertEquals($user->getProfile()->getId(), $updatedProfile->getId());
        $this->assertEquals($profileData["maritalStatus"], $updatedProfile->getMaritalStatus());
        $this->assertEquals($profileData["hasJob"], $updatedProfile->hasJob());
    }


    public function testSearchUsers() {
        self::$logger->info("Test searching users by filter");

        $profileFilter = new ProfileFilter();
        $profileFilter->setGender(ProfileConstants::GENDER_FEMALE);
        $filter = new UserFilter();
        $filter->setProfileFilter($profileFilter);

        $users = $this->userManager->search($filter);

        $this->assertNotNull($users);

        foreach ($users as $user) {
            $profile = $user->getProfile();
            $this->assertEquals($profileFilter->getGender(), $profile->getGender());
        }
    }


    private function userToArray(User $user): array {
        return array (
            "email" => $user->getEmail(),
            "firstname" => $user->getFirstname(),
            "lastname" => $user->getLastname(),
            "type" => $user->getType(),
            "enabled" => $user->isEnabled());
    }


    private function profileToArray(Profile $profile): array {
        return array (
            "gender" => $profile->getGender(),
            "phoneNumber" => $profile->getPhoneNumber(),
            "smoker" => $profile->isSmoker(),
            "houseProud" => $profile->isHouseProud(),
            "cook" => $profile->isCook(),
            "hasJob" => $profile->hasJob(),
            "maritalStatus" => $profile->getMaritalStatus(),
            "socialStatus" => $profile->getSocialStatus());
    }

}
