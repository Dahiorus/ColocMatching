<?php

namespace ColocMatching\CoreBundle\Tests\Repository\User;

use ColocMatching\CoreBundle\Tests\TestCase;
use ColocMatching\CoreBundle\Repository\User\UserRepository;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;

class UserRepositoryTest extends TestCase {

    /**
     * @var UserRepository
     */
    private $repository;


    protected function setUp() {
        $this->repository = self::getEntityManager()->getRepository(User::class);
    }


    public function testFindByPage() {
        self::$logger->info("Test finding users by page");

        /** @var AbstractFilter */
        $filter = new UserFilter();
        /** @var array */
        $users = $this->repository->findByPage($filter);
        /** @var int */
        $count = $this->repository->count();

        $this->assertNotNull($users);
        $this->assertEquals(count($users), $count);
    }


    public function testSelectFieldsByPage() {
        self::$logger->info("Test selecting users fields by page");

        /** @var AbstractFilter */
        $filter = new UserFilter();
        /** @var array */
        $users = $this->repository->selectFieldsByPage([ "id", "email"], $filter);

        $this->assertNotNull($users);

        foreach ($users as $user) {
            $this->assertArrayHasKey("id", $user);
            $this->assertArrayHasKey("email", $user);
            $this->assertArrayNotHasKey("gender", $user);
        }
    }


    public function testSelectFieldsFromOne() {
        self::$logger->info("Test selecting one announcement fields by page");

        /** @var array */
        $user = $this->repository->selectFieldsFromOne(1, [ "id", "email"]);

        $this->assertNotNull($user);
        $this->assertEquals(1, $user["id"]);
        $this->assertArrayHasKey("id", $user);
        $this->assertArrayHasKey("email", $user);
        $this->assertArrayNotHasKey("gender", $user);
    }

}