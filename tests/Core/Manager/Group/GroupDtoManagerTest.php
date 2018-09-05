<?php

namespace App\Tests\Core\Manager\Group;

use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\Group\GroupPictureDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserConstants;
use App\Core\Exception\InvalidCreatorException;
use App\Core\Exception\InvalidInviteeException;
use App\Core\Manager\Group\GroupDtoManager;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Mapper\Group\GroupDtoMapper;
use App\Tests\Core\Manager\AbstractManagerTest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GroupDtoManagerTest extends AbstractManagerTest
{
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
     * @throws \Exception
     */
    protected function createAndAssertEntity()
    {
        $this->creatorDto = $this->createUser();

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


    /**
     * @throws \Exception
     */
    private function createUser() : UserDto
    {
        $data = array ("email" => "user@yopmail.com",
            "firstName" => "John",
            "lastName" => "Smith",
            "plainPassword" => "secret1234",
            "type" => UserConstants::TYPE_SEARCH);

        return $this->userManager->create($data);
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
     * @throws \Exception
     */
    public function testCreateWithInvalidCreator()
    {
        $this->expectException(InvalidCreatorException::class);
        $this->creatorDto->setGroupId($this->testDto->getId());

        $this->manager->create($this->creatorDto, $this->testData);
    }


    /**
     * @throws \Exception
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
     * @throws \Exception
     */
    public function testAddAndGetMember()
    {
        $count = 2;

        for ($i = 1; $i <= $count; $i++)
        {
            $data = array (
                "email" => "user-$i@yopmail.com",
                "firstName" => "Member-$i",
                "lastName" => "Test",
                "plainPassword" => "secret1234",
                "type" => UserConstants::TYPE_SEARCH);
            $member = $this->userManager->create($data);

            $this->manager->addMember($this->testDto, $member);
        }

        /** @var UserDto[] $candidates */
        $candidates = $this->manager->getMembers($this->testDto);

        self::assertCount($count + 1, $candidates);

        // asserting candidates are users
        foreach ($candidates as $candidate)
        {
            self::assertInstanceOf(UserDto::class, $candidate);
            self::assertEquals(UserConstants::TYPE_SEARCH, $candidate->getType());
        }
    }


    /**
     * @throws \Exception
     */
    public function testAddProposalUserShouldThrowInvalidInvitee()
    {
        $data = array ("email" => "user-5@yopmail.com",
            "firstName" => "Candidate-5",
            "lastName" => "Test",
            "plainPassword" => "secret1234",
            "type" => UserConstants::TYPE_PROPOSAL);
        $member = $this->userManager->create($data);

        $this->expectException(InvalidInviteeException::class);

        $this->manager->addMember($this->testDto, $member);
    }


    /**
     * @throws \Exception
     */
    public function testAddAndRemoveMember()
    {
        $data = array (
            "email" => "user-to-remove@yopmail.com",
            "firstName" => "Member",
            "lastName" => "Test",
            "plainPassword" => "secret1234",
            "type" => UserConstants::TYPE_SEARCH);
        $member = $this->userManager->create($data);
        $this->manager->addMember($this->testDto, $member);

        $this->manager->removeMember($this->testDto, $member);

        $members = $this->manager->getMembers($this->testDto);

        self::assertCount(1, $members, "Expected group to have 1 member");
    }


    /**
     * @throws \Exception
     */
    public function testRemoveGroupCreatorShouldThrowInvalidInvitee()
    {
        $this->expectException(InvalidInviteeException::class);

        $this->manager->removeMember($this->testDto, $this->creatorDto);
    }


    /**
     * @throws \Exception
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
     * @throws \Exception
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

}