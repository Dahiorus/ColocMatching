<?php

namespace ColocMatching\CoreBundle\Tests\Repository\User;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use ColocMatching\CoreBundle\Repository\User\UserRepository;
use ColocMatching\CoreBundle\Tests\TestCase;
use Psr\Log\LoggerInterface;

class UserRepositoryTest extends TestCase {

    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;


    protected function setUp() {
        $this->repository = self::getRepository(User::class);
        $this->logger = self::getContainer()->get("logger");
    }


    protected function tearDown() {
    }


    public function testFindByPageable() {
        $this->logger->info("Test finding users with pagination");

        $filter = new PageableFilter();
        $users = $this->repository->findByPageable($filter);

        $this->assertNotNull($users);
        $this->assertTrue(count($users) <= $filter->getSize());
    }


    public function testSelectFieldsByPageable() {
        $this->logger->info("Test selecting fields of users with pagination");

        $fields = array ("email", "id");
        $filter = new PageableFilter();
        $users = $this->repository->findByPageable($filter, $fields);

        $this->assertNotNull($users);
        $this->assertTrue(count($users) <= $filter->getSize());

        foreach ($users as $user) {
            $this->assertArrayHasKey("email", $user);
            $this->assertArrayHasKey("id", $user);
            $this->assertArrayNotHasKey("description", $user);
        }
    }


    public function testFindById() {
        $this->logger->info("Test finding one user by Id");

        $user = $this->repository->findById(1);

        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(1, $user->getId());
    }


    public function testSelectFieldsById() {
        $this->logger->info("Test select fields from one user by Id");

        $fields = array ("email", "id");
        $user = $this->repository->findById(1, $fields);

        $this->assertNotNull($user);
        $this->assertEquals(1, $user["id"]);
        $this->assertArrayHasKey("email", $user);
        $this->assertArrayNotHasKey("description", $user);
    }


    public function testFindByFilter() {
        $this->logger->info("Test finding users by filter");

        $filter = new UserFilter();
        $users = $this->repository->findByFilter($filter);

        $this->assertNotNull($users);
        $this->assertTrue(count($users) <= $filter->getSize());
    }


    public function testSelectFieldsByFilter() {
        $this->logger->info("Test selecting fields of users by filter");

        $fields = array ("email", "id");
        $filter = new UserFilter();
        $users = $this->repository->findByFilter($filter, $fields);

        $this->assertNotNull($users);
        $this->assertTrue(count($users) <= $filter->getSize());

        foreach ($users as $user) {
            $this->assertArrayHasKey("email", $user);
            $this->assertArrayHasKey("id", $user);
            $this->assertArrayNotHasKey("description", $user);
        }
    }

}