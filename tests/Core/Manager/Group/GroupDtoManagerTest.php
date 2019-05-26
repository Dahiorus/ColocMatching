<?php

namespace App\Tests\Core\Manager\Group;

use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\Group\GroupPictureDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserType;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidInviteeException;
use App\Core\Manager\Group\GroupDtoManager;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Mapper\Group\GroupDtoMapper;
use App\Tests\Core\Manager\AbstractManagerTest;
use App\Tests\CreateUserTrait;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GroupDtoManagerTest extends AbstractManagerTest
{
    use CreateUserTrait;

    /** @var GroupDtoManagerInterface */
    protected $manager;

    /** @var GroupDtoMapper */
    protected $dtoMapper;

    /** @var GroupDto $dto */
    protected $testDto;

    /** @var UserDto */
    private $creatorDto;

    /** @var UserDtoManagerInterface */
    private $userManager;


    protected function initManager()
    {
        $this->userManager = $this->getService("coloc_matching.core.user_dto_manager");

        $this->dtoMapper = $this->getService("coloc_matching.core.group_dto_mapper");
        $entityValidator = $this->getService("coloc_matching.core.form_validator");
        $userDtoMapper = $this->getService("coloc_matching.core.user_dto_mapper");
        $pictureDtoMapper = $this->getService("coloc_matching.core.group_picture_dto_mapper");

        return new GroupDtoManager($this->logger, $this->em, $this->dtoMapper, $entityValidator, $userDtoMapper,
            $pictureDtoMapper);
    }


    protected function initTestData() : array
    {
        return array (
            "name" => "Group test",
            "description" => "Group test description",
            "budget" => 850
        );
    }


    /**
     * @throws Exception
     */
    protected function createAndAssertEntity()
    {
        $this->creatorDto = $this->createSearchUser($this->userManager, "search-user@test.fr");

        /** @var GroupDto $group */
        $group = $this->manager->create($this->creatorDto, $this->testData);
        $this->assertDto($group);

        return $group;
    }


    /**
     * @param GroupDto $dto
     */
    protected function assertDto($dto) : void
    {
        parent::assertDto($dto);
        self::assertNotEmpty($dto->getName(), "Expected group to have a name");
        self::assertTrue($dto->getBudget() >= 0, "Expected group to have a positive budget");
        self::assertNotEmpty($dto->getStatus(), "Expected group to have a status");
        self::assertNotEmpty($dto->getCreatorId(), "Expected group to have a creator");
    }


    protected function cleanData() : void
    {
        $this->manager->deleteAll();
        $this->userManager->deleteAll();
    }


    private function assertGroupPictureDto(GroupPictureDto $dto)
    {
        parent::assertDto($dto);
        self::assertNotEmpty($dto->getWebPath(), "Expected group picture to have a web path");
    }


    public function testCreateWithInvalidDataShouldThrowValidationErrors()
    {
        $this->testData["budget"] = -950;

        self::assertValidationError(function () {
            $this->manager->create($this->creatorDto, $this->testData);
        }, "budget");
    }


    /**
     * @throws Exception
     */
    public function testCreateAnotherGroup()
    {
        $this->manager->create($this->creatorDto, $this->initTestData());

        $announcements = $this->manager->listByCreator($this->creatorDto);

        self::assertEquals(2, $announcements->getCount(), "Expected to find 2 groups for the user");
    }


    /**
     * @throws Exception
     */
    public function testUpdateGroup()
    {
        $this->testData["description"] = "New description";

        $updatedGroup = $this->manager->update($this->testDto, $this->testData, true);

        $this->assertDto($updatedGroup);
        self::assertEquals($this->testData["description"], $updatedGroup->getDescription(),
            "Expected description to change");
    }


    public function testUpdateGroupWithInvalidDataShouldThrowValidationErrors()
    {
        $this->testData["name"] = "";
        $this->testData["budget"] = -95;

        self::assertValidationError(function () {
            $this->manager->update($this->testDto, $this->testData, true);
        }, "name", "budget");
    }


    /**
     * @throws Exception
     */
    public function testAddAndGetMember()
    {
        $count = 2;

        for ($i = 1; $i <= $count; $i++)
        {
            $member = $this->createSearchUser($this->userManager, "user-$i@yopmail.com");
            $this->manager->addMember($this->testDto, $member);
        }

        /** @var array $candidates */
        $candidates = $this->manager->getMembers($this->testDto);

        self::assertCount($count + 1, $candidates);

        // asserting candidates are users
        foreach ($candidates as $candidate)
        {
            self::assertInstanceOf(UserDto::class, $candidate);
            self::assertEquals(UserType::SEARCH, $candidate->getType());
        }
    }


    /**
     * @throws Exception
     */
    public function testAddProposalUserShouldThrowInvalidInvitee()
    {
        $member = $this->createProposalUser($this->userManager, "proposal@test.fr");

        $this->expectException(InvalidInviteeException::class);

        $this->manager->addMember($this->testDto, $member);
    }


    /**
     * @throws Exception
     */
    public function testAddAndRemoveMember()
    {
        $member = $this->createSearchUser($this->userManager, "user-to-remove@yopmail.com");
        $this->manager->addMember($this->testDto, $member);

        $this->manager->removeMember($this->testDto, $member);

        $members = $this->manager->getMembers($this->testDto);

        self::assertCount(1, $members, "Expected group to have 1 member");
    }


    /**
     * @throws Exception
     */
    public function testListByMember()
    {
        $member = $this->createSearchUser($this->userManager, "user-to-remove@yopmail.com");
        $this->manager->addMember($this->testDto, $member);

        $groups = $this->manager->listByMember($member);

        self::assertNotEmpty($groups, "Expected to find groups having the member");
    }


    /**
     * @throws Exception
     */
    public function testFindByMemberWithUnknownUserShouldThrowEntityNotFound()
    {
        $user = new UserDto();
        $user->setId(0);

        $this->expectException(EntityNotFoundException::class);

        $this->manager->listByMember($user);
    }


    /**
     * @throws Exception
     */
    public function testHasMemberWithUnknownUserShouldThrowEntityNotFound()
    {
        $user = new UserDto();
        $user->setId(0);

        $this->expectException(EntityNotFoundException::class);

        $this->manager->hasMember($this->testDto, $user);
    }


    /**
     * @throws Exception
     */
    public function testRemoveGroupCreatorShouldThrowInvalidInvitee()
    {
        $this->expectException(InvalidInviteeException::class);

        $this->manager->removeMember($this->testDto, $this->creatorDto);
    }


    /**
     * @throws Exception
     */
    public function testRemoveUnknownUserShouldThrowEntityNotFound()
    {
        $user = new UserDto();
        $user->setId(0);

        $this->expectException(EntityNotFoundException::class);

        $this->manager->removeMember($this->testDto, $user);
    }


    /**
     * @throws Exception
     */
    public function testHasMember()
    {
        $member = $this->createSearchUser($this->userManager, "user-to-remove@yopmail.com");
        $this->manager->addMember($this->testDto, $member);

        self::assertTrue($this->manager->hasMember($this->testDto, $member), "Expected the group to have the member");
    }


    /**
     * @throws Exception
     */
    public function testUploadGroupPicture()
    {
        $path = dirname(__FILE__) . "/../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "grp-img.jpg");

        $picture = $this->manager->uploadGroupPicture($this->testDto, $file);

        $this->assertGroupPictureDto($picture);
    }


    public function testUploadTextFileAsPictureShouldThrowValidationError()
    {
        $path = dirname(__FILE__) . "/../../Resources/file.txt";
        $file = new UploadedFile($path, "file.txt", "text/plain", null, true);

        self::assertValidationError(function () use ($file) {
            $this->manager->uploadGroupPicture($this->testDto, $file);
        }, "file");
    }


    /**
     * @throws Exception
     */
    public function testDeleteGroupPicture()
    {
        $path = dirname(__FILE__) . "/../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "grp-img.jpg");

        $picture = $this->manager->uploadGroupPicture($this->testDto, $file);
        $this->assertGroupPictureDto($picture);
        $this->testDto->setPicture($picture);

        $this->manager->deleteGroupPicture($this->testDto);

        /** @var GroupDto $groupDto */
        $groupDto = $this->manager->read($this->testDto->getId());
        self::assertEmpty($groupDto->getPicture());
    }


    /**
     * @throws Exception
     */
    public function testDeleteEmptyPicture()
    {
        $this->manager->deleteGroupPicture($this->testDto);

        /** @var GroupDto $groupDto */
        $groupDto = $this->manager->read($this->testDto->getId());
        self::assertEmpty($groupDto->getPicture());
    }


    /**
     * @throws Exception
     */
    public function testDeleteGroupWithPicture()
    {
        $path = dirname(__FILE__) . "/../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "grp-img.jpg");
        $this->manager->uploadGroupPicture($this->testDto, $file);

        $this->manager->delete($this->testDto);

        $this->expectException(EntityNotFoundException::class);
        $this->manager->read($this->testDto->getId());
    }


    /**
     * @test
     * @throws Exception
     */
    public function updateGroupWithPicture()
    {
        $path = dirname(__FILE__) . "/../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "grp-img.jpg");

        $this->manager->uploadGroupPicture($this->testDto, $file);

        /** @var GroupDto $group */
        $group = $this->manager->read($this->testDto->getId());
        $group = $this->manager->update($group, ["budget" => 1000], false);

        self::assertNotNull($group->getPicture());
        self::assertEquals(1000, $group->getBudget());
    }

}
