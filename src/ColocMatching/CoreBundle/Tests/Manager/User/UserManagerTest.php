<?php

namespace ColocMatching\CoreBundle\Tests\Manager\User;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use ColocMatching\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Unit tests for UserManagers
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
        self::$logger->info("Test creating a User");

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
        self::$logger->info("Test creating a User");

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
        $userData["gender"] = UserConstants::GENDER_MALE;
        $userData["lastname"] = "Php Unit";

        $updatedUser = $this->userManager->update($user, $userData);

        $this->assertEquals($updatedUser->getId(), $user->getId());
        $this->assertEquals(UserConstants::GENDER_MALE, $updatedUser->getGender());
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

        $file = $this->createTempFile();
        $updatedUser = $this->userManager->uploadProfilePicture($user, $file);

        $this->assertNotNull($updatedUser->getPicture());
    }


    public function testDeleteProfilePicture() {
        self::$logger->info("Test deleting a profile picture of a user");

        $user = $this->userManager->findByUsername("user@phpunit.fr");
        $this->assertNotNull($user);

        $this->userManager->deleteProfilePicture($user);
        $this->assertNull($user->getPicture());
    }


    private function userToArray(User $user): array {
        return array (
            "email" => $user->getEmail(),
            "firstname" => $user->getFirstname(),
            "lastname" => $user->getLastname(),
            "type" => $user->getType(),
            "gender" => $user->getGender(),
            "enabled" => $user->isEnabled());
    }


    private function createTempFile(): File {
        $file = tempnam(sys_get_temp_dir(), "tst");
        imagejpeg(imagecreatefromjpeg(dirname(__FILE__) . "/../../Resources/uploads/image.jpg"), $file);

        return new UploadedFile($file, "profile-img.jpg", "image/jpeg", null, null, true);
    }

}
