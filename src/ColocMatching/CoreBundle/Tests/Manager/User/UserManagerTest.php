<?php

namespace ColocMatching\CoreBundle\Tests\Manager\User;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\AnnouncementPreference;
use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Entity\User\UserPreference;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Form\Type\User\AnnouncementPreferenceType;
use ColocMatching\CoreBundle\Form\Type\User\ProfileType;
use ColocMatching\CoreBundle\Form\Type\User\RegistrationType;
use ColocMatching\CoreBundle\Form\Type\User\UserPreferenceType;
use ColocMatching\CoreBundle\Form\Type\User\UserType;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use ColocMatching\CoreBundle\Manager\User\UserManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use ColocMatching\CoreBundle\Repository\User\UserRepository;
use ColocMatching\CoreBundle\Tests\TestCase;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Group\GroupMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\ProfilePictureMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\CoreBundle\Validator\EntityValidator;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Unit tests for UserManager
 *
 * @author brondon.ung
 */
class UserManagerTest extends TestCase {

    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityValidator;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncorder;

    /**
     * @var LoggerInterface
     */
    private $logger;


    protected function setUp() {
        $entityClass = "CoreBundle:User\\User";
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->objectManager = $this->createMock(EntityManager::class);
        $this->entityValidator = $this->createMock(EntityValidator::class);
        $this->objectManager->expects($this->once())->method("getRepository")->with($entityClass)
            ->willReturn($this->userRepository);
        $this->passwordEncorder = self::getContainer()->get("security.password_encoder");
        $this->logger = self::getContainer()->get("logger");

        $this->userManager = new UserManager($this->objectManager, $entityClass, $this->entityValidator,
            $this->passwordEncorder, $this->logger);
    }


    protected function tearDown() {
        $this->logger->info("End test");
    }


    public function testList() {
        $this->logger->info("Test listing users");

        $filter = new PageableFilter();
        $expectedUsers = UserMock::createUserPage($filter, 40);

        $this->userRepository->expects($this->once())->method("findByPageable")->with($filter)->willReturn(
            $expectedUsers);

        $users = $this->userManager->list($filter);

        $this->assertNotNull($users);
        $this->assertEquals($expectedUsers, $users);
    }


    public function testCreateWithSuccess() {
        $this->logger->info("Test creating a user with success");

        $data = array (
            "email" => "user@test.fr",
            "plainPassword" => "password",
            "firstname" => "User",
            "lastname" => "Test",
            "type" => "proposal");
        $expectedUser = UserMock::createUser(1, $data["email"], $data["plainPassword"], $data["firstname"],
            $data["lastname"], $data["type"]);

        $this->entityValidator->expects($this->once())->method("validateEntityForm")
            ->with(new User(), $data, RegistrationType::class, true,
                array ("validation_groups" => array ("Create", "Default")))
            ->willReturn($expectedUser);
        $this->objectManager->expects($this->once())->method("persist")->with($expectedUser);

        $user = $this->userManager->create($data);

        $this->assertNotNull($user);
        $this->assertEquals($expectedUser, $user);
    }


    public function testCreateWithInvalidData() {
        $this->logger->info("Test creating a user with invalid form data");

        $data = array ("email" => "user@test.fr", "firstname" => "User", "type" => "search");

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with(new User(), $data,
            RegistrationType::class, true, array ("validation_groups" => array ("Create", "Default")))
            ->willThrowException($this->createMock(InvalidFormException::class));
        $this->objectManager->expects($this->never())->method("persist");
        $this->expectException(InvalidFormException::class);

        $this->userManager->create($data);
    }


    public function testReadWithSuccess() {
        $this->logger->info("Test reading a user with success");

        $id = 1;
        $expectedUser = UserMock::createUser($id, "user@test.fr", "password", "User", "Test",
            UserConstants::TYPE_SEARCH);

        $this->userRepository->expects($this->once())->method("findById")->with($id)->willReturn($expectedUser);

        $user = $this->userManager->read($id);

        $this->assertNotNull($user);
        $this->assertEquals($expectedUser, $user);
    }


    public function testReadWithNotFound() {
        $this->logger->info("Test reading a non exisitng user");

        $id = 1;

        $this->userRepository->expects($this->once())->method("findById")->with($id)->willReturn(null);
        $this->expectException(UserNotFoundException::class);

        $this->userManager->read($id);
    }


    public function testFindByUsernameWithSuccess() {
        $this->logger->info("Test finding a user by username");

        $username = "user@test.fr";
        $expectedUser = UserMock::createUser(1, $username, "password", "User", "Test", UserConstants::TYPE_SEARCH);

        $this->userRepository->expects($this->once())->method("findOneBy")->with(array ("email" => $username))->willReturn(
            $expectedUser);

        $user = $this->userManager->findByUsername($username);

        $this->assertNotNull($user);
        $this->assertEquals($expectedUser, $user);
    }


    public function testFindByUsernameWithNotFound() {
        $this->logger->info("Test finding a non existing user by username");

        $username = "user@test.fr";

        $this->userRepository->expects($this->once())->method("findOneBy")->with(array ("email" => $username))->willReturn(
            null);
        $this->expectException(UserNotFoundException::class);

        $this->userManager->findByUsername($username);
    }


    public function testFullUpdateWithSuccess() {
        $this->logger->info("Test updating a user with success");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $data = array (
            "email" => $user->getEmail(),
            "plainPassword" => $user->getPlainPassword(),
            "firstname" => "Toto",
            "lastname" => $user->getLastname(),
            "type" => UserConstants::TYPE_SEARCH);
        $expectedUser = UserMock::createUser($user->getId(), $data["email"], $data["plainPassword"], $data["firstname"],
            $data["lastname"], $data["type"]);

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with($user, $data,
            UserType::class, true)->willReturn($expectedUser);
        $this->objectManager->expects($this->once())->method("merge")->with($expectedUser);

        $updatedUser = $this->userManager->update($user, $data, true);

        $this->assertNotNull($updatedUser);
        $this->assertEquals($expectedUser, $updatedUser);
    }


    public function testFullUpdateWithInvalidData() {
        $this->logger->info("Test updating a user with invalid data");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $data = array (
            "email" => $user->getEmail(),
            "plainPassword" => $user->getPlainPassword(),
            "firstname" => null,
            "lastname" => $user->getLastname(),
            "type" => UserConstants::TYPE_SEARCH);

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with($user, $data,
            UserType::class, true)->willThrowException($this->createMock(InvalidFormException::class));
        $this->objectManager->expects($this->never())->method("persist");
        $this->expectException(InvalidFormException::class);

        $this->userManager->update($user, $data, true);
    }


    public function testPartialUpdateWithSuccess() {
        $this->logger->info("Test partial updating a user with success");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $data = array ("type" => UserConstants::TYPE_SEARCH);
        $expectedUser = UserMock::createUser($user->getId(), $user->getEmail(), $user->getPlainPassword(),
            $user->getFirstname(), $user->getLastname(), $data["type"]);

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with($user, $data,
            UserType::class, false)->willReturn($expectedUser);
        $this->objectManager->expects($this->once())->method("merge")->with($expectedUser);

        $updatedUser = $this->userManager->update($user, $data, false);

        $this->assertNotNull($updatedUser);
        $this->assertEquals($expectedUser, $updatedUser);
    }


    public function testPartialUpdateWithInvalidData() {
        $this->logger->info("Test partial updating a user with invalid data");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $data = array ("email" => null);

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with($user, $data,
            UserType::class, false)->willThrowException($this->createMock(InvalidFormException::class));
        $this->objectManager->expects($this->never())->method("persist");
        $this->expectException(InvalidFormException::class);

        $this->userManager->update($user, $data, false);
    }


    public function testDelete() {
        $this->logger->info("Test deleting a user");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);

        $this->objectManager->expects($this->once())->method("remove")->with($user);

        $this->userManager->delete($user);
    }


    public function testSearch() {
        $this->logger->info("Test searching users");

        $filter = new UserFilter();
        $expectedUsers = UserMock::createUserPage($filter, 50);

        $this->userRepository->expects($this->once())->method("findByFilter")->with($filter)->willReturn($expectedUsers);

        $users = $this->userManager->search($filter);

        $this->assertNotNull($users);
        $this->assertEquals($expectedUsers, $users);
    }


    public function testUploadProfilePicture() {
        $this->logger->info("Test uploading a profile picture");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $file = $this->createTempFile(dirname(__FILE__) . "/../../Resources/uploads/image.jpg", "user-img.jpg");
        $expectedPicture = ProfilePictureMock::createPicture(1, $file, "picture-test.jpg");

        $this->entityValidator->expects($this->once())->method("validatePictureForm")->with(new ProfilePicture(),
            $file, ProfilePicture::class)->willReturn($expectedPicture);
        $this->objectManager->expects($this->once())->method("persist")->with($user);

        $picture = $this->userManager->uploadProfilePicture($user, $file);

        $this->assertNotNull($picture);
        $this->assertEquals($expectedPicture, $picture);
    }


    public function testDeleteProfilePictureWithSuccess() {
        $this->logger->info("Test deleting the profile picture of a user");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $file = $this->createTempFile(dirname(__FILE__) . "/../../Resources/uploads/image.jpg", "group-img.jpg");
        $user->setPicture(ProfilePictureMock::createPicture(1, $file, "picture-test.jpg"));

        $this->objectManager->expects($this->once())->method("remove")->with($user->getPicture());

        $this->userManager->deleteProfilePicture($user);

        $this->assertNull($user->getPicture());
    }


    public function testDeleteProfilePictureWithFailure() {
        $this->logger->info("Test deleting a non existing profile picture of a user");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);

        $this->objectManager->expects($this->never())->method("remove");

        $this->userManager->deleteProfilePicture($user);
    }


    public function testUpdateProfileWithSuccess() {
        $this->logger->info("Test updating a user's profile with success");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $data = array ("gender" => "female", "hasJob" => true);
        $expectedProfile = new Profile();
        $expectedProfile->setGender($data["gender"])->setHasJob($data["hasJob"]);

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with($user->getProfile(), $data,
            ProfileType::class, false)->willReturn($expectedProfile);

        $profile = $this->userManager->updateProfile($user, $data, false);

        $this->assertNotNull($profile);
        $this->assertEquals($expectedProfile, $profile);
    }


    public function testUpdateUserPreferenceWithSuccess() {
        $this->logger->info("Test updating a user's user preference with success");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $data = array ("gender" => "female", "ageStart" => 20);
        $expectedPreference = new UserPreference();
        $expectedPreference->setGender($data["gender"])->setAgeStart($data["ageStart"]);

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with($user->getUserPreference(),
            $data, UserPreferenceType::class, false)->willReturn($expectedPreference);
        $this->objectManager->expects(self::once())->method("merge")->with($expectedPreference);

        $preference = $this->userManager->updateUserPreference($user, $data, false);

        $this->assertNotNull($preference);
        $this->assertEquals($expectedPreference, $preference);
    }


    public function testUpdateAnnouncementPreferenceWithSuccess() {
        $this->logger->info("Test updating a user's announcement preference with success");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $data = array ("rentPriceStart" => 500, "rentPriceEnd" => 899);
        $expectedPreference = new AnnouncementPreference();
        $expectedPreference->setRentPriceStart($data["rentPriceStart"])->setRentPriceEnd($data["rentPriceEnd"]);

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with(
            $user->getAnnouncementPreference(), $data, AnnouncementPreferenceType::class, false)
            ->willReturn($expectedPreference);
        $this->objectManager->expects(self::once())->method("merge")->with($expectedPreference);

        $preference = $this->userManager->updateAnnouncementPreference($user, $data, false);

        $this->assertNotNull($preference);
        $this->assertEquals($expectedPreference, $preference);
    }


    public function testBanProposalWithAnnouncement() {
        $this->logger->info("Test banning a proposal user with announcement");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $user->setStatus(UserConstants::STATUS_ENABLED);
        $user->setAnnouncement(AnnouncementMock::createAnnouncement(1, $user, "Paris 75014", "Announcement test",
            Announcement::TYPE_RENT, 950, new \DateTime()));

        $this->objectManager->expects(self::once())->method("merge")->with($user);
        $this->objectManager->expects(self::once())->method("remove")->with($user->getAnnouncement());

        $bannedUser = $this->userManager->updateStatus($user, UserConstants::STATUS_BANNED);

        self::assertEquals(UserConstants::STATUS_BANNED, $bannedUser->getStatus());
    }


    public function testBanSearchWithGroup() {
        $this->logger->info("Test banning a user in search with a group");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $user->setStatus(UserConstants::STATUS_ENABLED);
        $user->setGroup(GroupMock::createGroup(1, $user, "Group test", "Group description"));

        $this->objectManager->expects(self::once())->method("merge")->with($user);
        $this->objectManager->expects(self::once())->method("remove")->with($user->getGroup());

        $bannedUser = $this->userManager->updateStatus($user, UserConstants::STATUS_BANNED);

        self::assertEquals(UserConstants::STATUS_BANNED, $bannedUser->getStatus());
    }


    public function testBanSearchWithGroupAndMembers() {
        $this->logger->info("Test banning a user in search with a group having members");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $user->setStatus(UserConstants::STATUS_ENABLED);

        $group = GroupMock::createGroup(1, $user, "Group test", "Group description");
        $group->addMember(UserMock::createUser(2, "member-1@test.fr", "password", "Member", "Test",
            UserConstants::TYPE_SEARCH));
        $user->setGroup($group);

        $this->objectManager->expects(self::exactly(2))->method("merge")->withConsecutive($user->getGroup(), $user);

        $bannedUser = $this->userManager->updateStatus($user, UserConstants::STATUS_BANNED);

        self::assertEquals(UserConstants::STATUS_BANNED, $bannedUser->getStatus());
    }


    public function testBan() {
        $this->logger->info("Test banning a user");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $user->setStatus(UserConstants::STATUS_ENABLED);

        $this->objectManager->expects(self::once())->method("merge")->with($user);

        $bannedUser = $this->userManager->updateStatus($user, UserConstants::STATUS_BANNED);

        self::assertEquals(UserConstants::STATUS_BANNED, $bannedUser->getStatus());
    }


    public function testEnable() {
        $this->logger->info("Test enabling a user");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);

        $this->objectManager->expects(self::once())->method("merge")->with($user);

        $enabledUser = $this->userManager->updateStatus($user, UserConstants::STATUS_ENABLED);

        self::assertEquals(UserConstants::STATUS_ENABLED, $enabledUser->getStatus());
    }


    public function testEnableProposal() {
        $this->logger->info("Test enabling a proposal user");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $user->setAnnouncement(AnnouncementMock::createAnnouncement(1, $user, "Paris 75014", "Announcement test",
            Announcement::TYPE_RENT, 950, new \DateTime()));
        $user->getAnnouncement()->setStatus(Announcement::STATUS_DISABLED);

        $this->objectManager->expects(self::exactly(2))->method("merge")->withConsecutive($user->getAnnouncement(),
            $user);

        $enabledUser = $this->userManager->updateStatus($user, UserConstants::STATUS_ENABLED);

        self::assertEquals(UserConstants::STATUS_ENABLED, $enabledUser->getStatus());
    }


    public function testEnableSearchWithGroup() {
        $this->logger->info("Test enabling a user in search with a group");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $user->setGroup(GroupMock::createGroup(1, $user, "Group test", "Group description"));
        $user->getGroup()->setStatus(Group::STATUS_CLOSED);

        $this->objectManager->expects(self::exactly(2))->method("merge")->withConsecutive($user->getGroup(), $user);

        $enabledUser = $this->userManager->updateStatus($user, UserConstants::STATUS_ENABLED);

        self::assertEquals(UserConstants::STATUS_ENABLED, $enabledUser->getStatus());
    }


    public function testDisable() {
        $this->logger->info("Test disabling a user");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);

        $this->objectManager->expects(self::once())->method("merge")->with($user);

        $disabledUser = $this->userManager->updateStatus($user, UserConstants::STATUS_VACATION);

        self::assertEquals(UserConstants::STATUS_VACATION, $disabledUser->getStatus());
    }


    public function testDisableProposal() {
        $this->logger->info("Test disabling a proposal user");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $user->setAnnouncement(AnnouncementMock::createAnnouncement(1, $user, "Paris 75014", "Announcement test",
            Announcement::TYPE_RENT, 950, new \DateTime()));
        $user->getAnnouncement()->setStatus(Announcement::STATUS_ENABLED);

        $this->objectManager->expects(self::exactly(2))->method("merge")->withConsecutive($user->getAnnouncement(),
            $user);

        $disabledUser = $this->userManager->updateStatus($user, UserConstants::STATUS_VACATION);

        self::assertEquals(UserConstants::STATUS_VACATION, $disabledUser->getStatus());
    }


    public function testDisableSearchWithGroup() {
        $this->logger->info("Test disabling a user in search with a group");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $user->setGroup(GroupMock::createGroup(1, $user, "Group test", "Group description"));
        $user->getGroup()->setStatus(Group::STATUS_OPENED);

        $this->objectManager->expects(self::exactly(2))->method("merge")->withConsecutive($user->getGroup(), $user);

        $disabledUser = $this->userManager->updateStatus($user, UserConstants::STATUS_VACATION);

        self::assertEquals(UserConstants::STATUS_VACATION, $disabledUser->getStatus());
    }

}
