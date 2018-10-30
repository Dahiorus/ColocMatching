<?php

namespace App\Tests\Core\Manager\Alert;

use App\Core\DTO\Alert\AlertDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Alert\NotificationType;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\AnnouncementType;
use App\Core\Manager\Alert\AlertDtoManager;
use App\Core\Manager\Alert\AlertDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Mapper\Alert\AlertDtoMapper;
use App\Core\Repository\Filter\AnnouncementFilter;
use App\Core\Repository\Filter\UserFilter;
use App\Tests\Core\Manager\AbstractManagerTest;
use App\Tests\CreateUserTrait;

class AlertDtoManagerTest extends AbstractManagerTest
{
    use CreateUserTrait;

    /** @var AlertDtoManagerInterface */
    protected $manager;

    /** @var AlertDtoMapper */
    protected $dtoMapper;

    /** @var AlertDto */
    protected $testDto;

    /** @var UserDtoManagerInterface */
    private $userManager;


    protected function initManager()
    {
        $this->userManager = $this->getService("coloc_matching.core.user_dto_manager");

        $formValidator = $this->getService("coloc_matching.core.form_validator");
        $userDtoMapper = $this->getService("coloc_matching.core.user_dto_mapper");
        $this->dtoMapper = $this->getService("coloc_matching.core.alert_dto_mapper");

        return new AlertDtoManager($this->logger, $this->em, $this->dtoMapper, $formValidator,
            $userDtoMapper);
    }


    /**
     * @return array
     * @throws \Exception
     */
    protected function initTestData() : array
    {
        return array (
            "name" => "alert test",
            "notificationType" => NotificationType::EMAIL,
            "searchPeriod" => "P0M2D",
            "filter" => array (
                "pageable" => array (
                    "page" => 2,
                    "size" => 10,
                    "sorts" => array (
                        array ("property" => "createdAt", "direction" => "desc"),
                        array ("property" => "title", "direction" => "asc")
                    )
                ),
                "withDescription" => true,
                "status" => Announcement::STATUS_ENABLED,
                "types" => [AnnouncementType::RENT],
            ),
        );
    }


    protected function createAndAssertEntity()
    {
        $userDto = $this->createSearchUser($this->userManager, "user@test.fr");

        /** @var AlertDto $dto */
        $dto = $this->manager->create($userDto, AnnouncementFilter::class, $this->testData);
        $this->assertDto($dto);

        return $dto;
    }


    /**
     * @throws \Exception
     */
    protected function cleanData() : void
    {
        $this->manager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @param AlertDto $dto
     */
    protected function assertDto($dto) : void
    {
        parent::assertDto($dto);
        self::assertNotNull($dto->getFilter(), "An alert must have a filter");
        self::assertInstanceOf(AnnouncementFilter::class, $dto->getFilter(),
            "Expected the filter to be an instance of " . AnnouncementFilter::class);
        self::assertNotEmpty($dto->getName(), "An alert must have a name");
        self::assertNotEmpty($dto->getSearchPeriod(), "An alert must have a search period");
        self::assertNotEmpty($dto->getNotificationType(), "An alert must have a notification type");
    }


    /**
     * @test
     */
    public function createAlertWithWrongFilterClassShouldThrowInvalidForm()
    {
        $user = new UserDto();
        $user->setId(1);

        self::assertValidationError(function () use ($user) {
            $this->manager->create($user, UserFilter::class, $this->testData);
        }, "filter");
    }


    /**
     * @test
     */
    public function createAlertWithInvalidDataShouldThrowInvalidForm()
    {
        $user = new UserDto();
        $user->setId(1);

        $this->testData["notificationType"] = "qlsdfjsldfj";
        $this->testData["name"] = "";

        self::assertValidationError(function () use ($user) {
            $this->manager->create($user, AnnouncementFilter::class, $this->testData);
        }, "notificationType", "name");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function updateAlert()
    {
        $this->testData["name"] = "updated alert";
        $this->testData["filter"]["withPictures"] = true;
        $this->testData["filter"]["roomCount"] = 5;

        $updatedAlert = $this->manager->update($this->testDto, $this->testData, true);

        $this->assertDto($updatedAlert);
        self::assertEquals($this->testData["name"], $updatedAlert->getName(), "Expected the alert name to be updated");

        /** @var AnnouncementFilter $filter */
        $filter = $updatedAlert->getFilter();
        self::assertEquals($this->testData["filter"]["withPictures"], $filter->withPictures(),
            "Expected filter 'withPictures' to be true");
        self::assertEquals($this->testData["filter"]["roomCount"], $filter->getRoomCount(),
            "Expected filter 'roomCount' to be updated");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function updateAlertWithInvalidDataShouldThrowInvalidForm()
    {
        $this->testData["name"] = "updated alert";
        $this->testData["filter"]["withPictures"] = true;
        $this->testData["filter"]["roomCount"] = "fklqjfld";
        $this->testData["notificationType"] = "kjqlfjs";

        self::assertValidationError(function () {
            $this->manager->update($this->testDto, $this->testData, true);
        }, "notificationType", "roomCount");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findUserAlerts()
    {
        /** @var UserDto $user */
        $user = $this->userManager->read($this->testDto->getUserId());
        /** @var AlertDto[] $alerts */
        $alerts = $this->manager->findByUser($user)->getContent();

        self::assertNotEmpty($alerts, "Expected to find alerts for the user [$user]");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function countUserAlerts()
    {
        /** @var UserDto $user */
        $user = $this->userManager->read($this->testDto->getUserId());
        /** @var int $count */
        $count = $this->manager->countByUser($user);

        self::assertTrue($count > 0, "Expected to find alerts for the user [$user]");
    }

}
