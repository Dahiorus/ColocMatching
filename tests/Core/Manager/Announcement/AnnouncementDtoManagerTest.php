<?php

namespace App\Tests\Core\Manager\Announcement;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\Announcement\AnnouncementPictureDto;
use App\Core\DTO\Announcement\CommentDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\User\UserConstants;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidCreatorException;
use App\Core\Exception\InvalidInviteeException;
use App\Core\Manager\Announcement\AnnouncementDtoManager;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Mapper\Announcement\AnnouncementDtoMapper;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Tests\Core\Manager\AbstractManagerTest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AnnouncementDtoManagerTest extends AbstractManagerTest
{
    /** @var AnnouncementDtoManagerInterface */
    protected $manager;

    /** @var AnnouncementDtoMapper */
    protected $dtoMapper;

    /** @var AnnouncementDto $dto */
    protected $testDto;

    /** @var UserDto */
    private $creatorDto;

    /** @var UserDtoManagerInterface */
    private $userManager;


    protected function initManager()
    {
        $this->userManager = $this->getService("coloc_matching.core.user_dto_manager");

        $this->dtoMapper = $this->getService("coloc_matching.core.announcement_dto_mapper");
        $entityValidator = $this->getService("coloc_matching.core.form_validator");
        $userDtoMapper = $this->getService("coloc_matching.core.user_dto_mapper");
        $housingDtoMapper = $this->getService("coloc_matching.core.housing_dto_mapper");
        $commentDtoMapper = $this->getService("coloc_matching.core.comment_dto_mapper");
        $pictureDtoMapper = $this->getService("coloc_matching.core.announcement_picture_dto_mapper");

        return new AnnouncementDtoManager($this->logger, $this->em, $this->dtoMapper, $entityValidator, $userDtoMapper,
            $housingDtoMapper, $commentDtoMapper, $pictureDtoMapper);
    }


    protected function initTestData() : array
    {
        return array (
            "title" => "Test announcement",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => 1200,
            "location" => "Paris 75020",
            "startDate" => (new \DateTime())->format("Y-m-d")
        );
    }


    /**
     * @return AnnouncementDto
     * @throws \Exception
     */
    protected function createAndAssertEntity()
    {
        $this->creatorDto = $this->createUser();

        /** @var AnnouncementDto $dto */
        $dto = $this->manager->create($this->creatorDto, $this->testData);

        $this->assertDto($dto);

        return $dto;
    }


    /**
     * @param AnnouncementDto $dto
     */
    protected function assertDto($dto) : void
    {
        parent::assertDto($dto);
        self::assertNotEmpty($dto->getTitle(), "Expected announcement to have a title");
        self::assertNotEmpty($dto->getType(), "Expected announcement to have a type");
        self::assertNotEmpty($dto->getRentPrice(), "Expected announcement to have a rent price");
        self::assertNotEmpty($dto->getLocation(), "Expected announcement to have a location");
        self::assertNotEmpty($dto->getStartDate(), "Expected announcement to have a start date");
        self::assertNotEmpty($dto->getCreatorId(), "Expected announcement to have a creator");
    }


    protected function cleanData() : void
    {
        $this->manager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @return UserDto
     * @throws \Exception
     */
    private function createUser() : UserDto
    {
        $data = array ("email" => "user@yopmail.com",
            "firstName" => "John",
            "lastName" => "Smith",
            "plainPassword" => "secret1234",
            "type" => UserConstants::TYPE_PROPOSAL);

        return $this->userManager->create($data);
    }


    private function assertAnnouncementPictureDto(AnnouncementPictureDto $dto)
    {
        parent::assertDto($dto);
        self::assertNotEmpty($dto->getWebPath(), "Expected announcement picture to have a web path");
        self::assertEquals($this->testDto->getId(), $dto->getAnnouncementId(),
            "Expected announcement id to be equal to " . $this->testDto->getId());
    }


    public function testCreateWithInvalidDataShouldThrowValidationErrors()
    {
        $this->testData["rentPrice"] = -260;
        $this->testData["title"] = "";

        self::assertValidationError(function () {
            $this->manager->create($this->creatorDto, $this->testData);
        }, "rentPrice", "title");
    }


    /**
     * @throws \Exception
     */
    public function testCreateWithUserHavingAnnouncementShouldThrowInvalidCreatorException()
    {
        $this->expectException(InvalidCreatorException::class);
        $this->creatorDto->setAnnouncementId($this->testDto->getId());

        $this->manager->create($this->creatorDto, $this->testData);
    }


    /**
     * @throws \Exception
     */
    public function testUpdate()
    {
        $this->testData["title"] = "Modified announcement";

        /** @var AnnouncementDto $announcement */
        $announcement = $this->manager->update($this->testDto, $this->testData, true);

        $this->assertDto($announcement);
        self::assertEquals($this->testData["title"], $announcement->getTitle(),
            "Expected announcement to have a new title");
    }


    /**
     * @throws \Exception
     */
    public function testUpdateWithMissingDataShouldThrowValidationError()
    {
        $this->testData["type"] = null;
        $this->testData["title"] = "";

        self::assertValidationError(function () {
            $this->manager->update($this->testDto, $this->testData, true);
        }, "type", "title");
    }


    /**
     * @throws \Exception
     */
    public function testGetHousing()
    {
        $housing = $this->manager->getHousing($this->testDto);

        parent::assertDto($housing);
    }


    /**
     * @throws \Exception
     */
    public function testUpdateHousing()
    {
        $data = array (
            "roomCount" => 3,
            "bathroomCount" => 1,
            "bedroomCount" => 2,
            "surfaceArea" => 20
        );

        $housing = $this->manager->updateHousing($this->testDto, $data, true);

        parent::assertDto($housing);
        self::assertEquals($data["roomCount"], $housing->getRoomCount());
        self::assertEquals($data["bathroomCount"], $housing->getBathroomCount());
        self::assertEquals($data["bedroomCount"], $housing->getBedroomCount());
        self::assertEquals($data["surfaceArea"], $housing->getSurfaceArea());
    }


    public function testUpdateHousingWithInvalidDataShouldThrowValidationErrors()
    {
        $data = array (
            "roomCount" => -1,
            "bathroomCount" => -9,
            "surfaceArea" => -20
        );

        self::assertValidationError(function () use ($data) {
            $this->manager->updateHousing($this->testDto, $data, true);
        }, "roomCount", "bathroomCount", "surfaceArea");
    }


    /**
     * @throws \Exception
     */
    public function testAddAndGetCandidates()
    {
        $count = 2;

        // adding candidates
        for ($i = 1; $i <= $count; $i++)
        {
            $data = array ("email" => "user-$i@yopmail.com",
                "firstName" => "Candidate-$i",
                "lastName" => "Test",
                "plainPassword" => "secret1234",
                "type" => UserConstants::TYPE_SEARCH);
            $candidate = $this->userManager->create($data);

            $this->manager->addCandidate($this->testDto, $candidate);
        }

        /** @var UserDto[] $candidates */
        $candidates = $this->manager->getCandidates($this->testDto);

        self::assertCount($count, $candidates);

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
        $candidate = $this->userManager->create($data);

        $this->expectException(InvalidInviteeException::class);

        $this->manager->addCandidate($this->testDto, $candidate);
    }


    /**
     * @throws \Exception
     */
    public function testAddAnnouncementCreatorShouldThrowInvalidInvitee()
    {
        $this->expectException(InvalidInviteeException::class);

        $this->manager->addCandidate($this->testDto, $this->creatorDto);
    }


    /**
     * @throws \Exception
     */
    public function testAddAndRemoveCandidates()
    {
        $data = array ("email" => "user-to-remove@yopmail.com",
            "firstName" => "Candidate-to-remove",
            "lastName" => "Test",
            "plainPassword" => "secret1234",
            "type" => UserConstants::TYPE_SEARCH);
        $candidate = $this->userManager->create($data);
        $this->manager->addCandidate($this->testDto, $candidate);

        $this->manager->removeCandidate($this->testDto, $candidate);

        $candidates = $this->manager->getCandidates($this->testDto);

        self::assertEmpty($candidates, "Expected announcement to have no candidate");
    }


    /**
     * @throws \Exception
     */
    public function testRemoveUnknownCandidate()
    {
        $count = count($this->manager->getCandidates($this->testDto));

        $this->expectException(EntityNotFoundException::class);

        $this->manager->removeCandidate($this->testDto, $this->creatorDto);

        $candidates = $this->manager->getCandidates($this->testDto);

        self::assertCount($count, $candidates, "Expected announcement to have $count candidate(s)");
    }


    /**
     * @throws \Exception
     */
    public function testCreateAndGetAndDeleteComments()
    {
        // creating comment
        $data = array ("message" => "This is a comment", "rate" => 4);
        $comment = $this->manager->createComment($this->testDto, $this->creatorDto, $data);

        parent::assertDto($comment);
        self::assertEquals($data["message"], $comment->getMessage());
        self::assertEquals($data["rate"], $comment->getRate());
        self::assertEquals($this->creatorDto->getId(), $comment->getAuthorId());

        // getting comments
        $comments = $this->manager->getComments($this->testDto, new PageRequest());

        self::assertNotEmpty($comments, "Expected to find comments");
        self::assertCount(1, $comments, "Expected to find 1 comment");

        // deleting comment
        $this->manager->deleteComment($this->testDto, $comment);

        self::assertEmpty($this->manager->getComments($this->testDto, new PageRequest()),
            "Expected to find no comments");
    }


    public function testCreateCommentWithInvalidDateShouldThrowValidationErrors()
    {
        $data = array ("message" => "This is a comment", "rate" => -99);

        self::assertValidationError(function () use ($data) {
            $this->manager->createComment($this->testDto, $this->creatorDto, $data);
        }, "rate");
    }


    /**
     * @throws \Exception
     */
    public function testDeleteUnknownComment()
    {
        $count = count($this->manager->getComments($this->testDto, new PageRequest()));

        $comment = new CommentDto();
        $comment->setId(999);

        $this->expectException(EntityNotFoundException::class);

        $this->manager->deleteComment($this->testDto, $comment);

        self::assertCount($count, $this->manager->getComments($this->testDto, new PageRequest()),
            "Expected to find $count comment(s)");
    }


    /**
     * @throws \Exception
     */
    public function testUploadAndDeleteAnnouncementPicture()
    {
        // uploading the picture
        $path = dirname(__FILE__) . "/../../Resources/uploads/appartement.jpg";
        $file = $this->createTmpJpegFile($path, "announcement-img.jpg");

        $picture = $this->manager->uploadAnnouncementPicture($this->testDto, $file);

        $this->assertAnnouncementPictureDto($picture);

        // deleting the picture
        $this->manager->deleteAnnouncementPicture($this->testDto, $picture);

        /** @var AnnouncementDto $announcement */
        $announcement = $this->manager->read($this->testDto->getId());
        self::assertEmpty($announcement->getPictures(), "Expected to find no picture in the announcement");
    }


    public function testUploadTextFileAsPictureShouldThrowValidationError()
    {
        $path = dirname(__FILE__) . "/../../Resources/file.txt";
        $file = new UploadedFile($path, "file.txt", "text/plain", null, true);

        self::assertValidationError(function () use ($file) {
            $this->manager->uploadAnnouncementPicture($this->testDto, $file);
        }, "file");
    }


    /**
     * @throws \Exception
     */
    public function testDeleteUnknownAnnouncementPicture()
    {
        /** @var AnnouncementDto $announcement */
        $announcement = $this->manager->read($this->testDto->getId());
        $count = $announcement->getPictures()->count();

        $picture = new AnnouncementPictureDto();
        $picture->setId(999);
        $picture->setAnnouncementId($this->testDto->getId() + 1);

        $this->expectException(EntityNotFoundException::class);

        // deleting the picture
        $this->manager->deleteAnnouncementPicture($this->testDto, $picture);

        $announcement = $this->manager->read($this->testDto->getId());
        self::assertCount($count, $announcement->getPictures(), "Expected to find $count pictures in the announcement");
    }

}