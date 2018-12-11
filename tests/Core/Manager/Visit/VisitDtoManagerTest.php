<?php

namespace App\Tests\Core\Manager\Visit;

use App\Core\DTO\Visit\VisitDto;
use App\Core\Entity\User\User;
use App\Core\Entity\User\UserStatus;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Manager\Visit\VisitDtoManager;
use App\Core\Manager\Visit\VisitDtoManagerInterface;
use App\Core\Repository\Filter\VisitFilter;
use App\Tests\Core\Manager\AbstractManagerTest;
use App\Tests\CreateUserTrait;

class VisitDtoManagerTest extends AbstractManagerTest
{
    use CreateUserTrait;

    /** @var VisitDtoManagerInterface */
    protected $manager;


    protected function initManager()
    {
        $this->dtoMapper = $this->getService("coloc_matching.core.visit_dto_mapper");
        $userDtoMapper = $this->getService("coloc_matching.core.user_dto_mapper");

        return new VisitDtoManager($this->logger, $this->em, $this->dtoMapper, $userDtoMapper);
    }


    protected function initTestData() : array
    {
        return [];
    }


    protected function createAndAssertEntity()
    {
        /** @var UserDtoManagerInterface $userManager */
        $userManager = $this->getService("coloc_matching.core.user_dto_manager");
        $visitor = $this->createSearchUser($userManager, "visitor@yopmail.com", UserStatus::ENABLED);
        $visited = $this->createProposalUser($userManager, "visited@yopmail.com", UserStatus::VACATION);

        $visit = $this->manager->create($visitor, $visited);
        $this->assertDto($visit);

        return $visit;
    }


    protected function cleanData() : void
    {
        /** @var UserDtoManagerInterface $userManager */
        $userManager = $this->getService("coloc_matching.core.user_dto_manager");
        $this->manager->deleteAll();
        $userManager->deleteAll();
    }


    /**
     * @param VisitDto $dto
     */
    protected function assertDto($dto) : void
    {
        parent::assertDto($dto);
        self::assertNotEmpty($dto->getVisitorId(), "A visit must have a visitor");
        self::assertNotEmpty($dto->getVisitedClass(), "A visit must have a visited class");
        self::assertNotEmpty($dto->getVisitedId(), "A visit must have a visited id");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function countByFilter()
    {
        $filter = new VisitFilter();
        $filter->setVisitedClass(User::class);

        $count = $this->manager->countByFilter($filter);

        self::assertEquals(1, $count, "Expected to find 1 visit");
    }

}
