<?php

namespace ColocMatching\CoreBundle\Tests\Repository\Invitation;

use ColocMatching\CoreBundle\Entity\Visit\Visit;
use ColocMatching\CoreBundle\Repository\Filter\InvitationFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Invitation\InvitationRepository;
use ColocMatching\CoreBundle\Tests\TestCase;
use Psr\Log\LoggerInterface;

abstract class InvitationRepositoryTest extends TestCase {

    /**
     * @var InvitationRepository
     */
    protected $repository;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    protected function setUp() {
        $this->logger = self::getContainer()->get("logger");
    }


    protected function tearDown() {
        $this->logger->info("End test");
    }


    public function testFindByPageable() {
        $this->logger->info("Test finding invitations with pagination");

        $filter = new PageableFilter();
        $invitations = $this->repository->findByPageable($filter);

        $this->assertNotNull($invitations);
        $this->assertTrue(count($invitations) <= $filter->getSize());
    }


    public function testSelectFieldsByPageable() {
        $this->logger->info("Test selecting fields of invitation with pagination");

        $fields = array ("id", "message");
        $filter = new PageableFilter();
        $invitations = $this->repository->findByPageable($filter, $fields);

        $this->assertNotNull($invitations);
        $this->assertTrue(count($invitations) <= $filter->getSize());

        foreach ($invitations as $invitation) {
            $this->assertArrayHasKey("message", $invitation);
            $this->assertArrayHasKey("id", $invitation);
            $this->assertArrayNotHasKey("createdAt", $invitation);
        }
    }


    public function testFindById() {
        $this->logger->info("Test finding one invitation by Id");

        $invitation = $this->repository->findById(1);

        $this->assertNotNull($invitation);
        $this->assertInstanceOf(Visit::class, $invitation);
        $this->assertEquals(1, $invitation->getId());
    }


    public function testSelectFieldsById() {
        $this->logger->info("Test select fields from one invitation by Id");

        $fields = array ("createdAt", "id");
        $invitation = $this->repository->findById(1, $fields);

        $this->assertNotNull($invitation);
        $this->assertEquals(1, $invitation["id"]);
        $this->assertArrayHasKey("createdAt", $invitation);
        $this->assertArrayNotHasKey("message", $invitation);
    }


    public function testFindByFilter() {
        $this->logger->info("Test finding invitations by filter");

        $filter = new InvitationFilter();
        $invitations = $this->repository->findByFilter($filter);

        $this->assertNotNull($invitations);
        $this->assertTrue(count($invitations) <= $filter->getSize());
    }


    public function testSelectFieldsByFilter() {
        $this->logger->info("Test selecting fields of invitations by filter");

        $fields = array ("message", "id");
        $filter = new InvitationFilter();
        $invitations = $this->repository->findByFilter($filter, $fields);

        $this->assertNotNull($invitations);
        $this->assertTrue(count($invitations) <= $filter->getSize());

        foreach ($invitations as $invitation) {
            $this->assertArrayHasKey("message", $invitation);
            $this->assertArrayHasKey("id", $invitation);
            $this->assertArrayNotHasKey("lastUpdate", $invitation);
        }
    }

}