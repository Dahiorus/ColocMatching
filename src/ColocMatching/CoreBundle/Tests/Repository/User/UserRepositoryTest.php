<?php

namespace ColocMatching\CoreBundle\Tests\Repository\User;

use ColocMatching\CoreBundle\Tests\TestCase;
use ColocMatching\CoreBundle\Repository\User\UserRepository;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Entity\User\ProfileConstants;

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

        $this->assertNotNull($users);
        $this->assertTrue(count($users) <= $filter->getSize());
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


    public function testFindByFilter() {
        self::$logger->info("Test finding announcements by filter");

        /** @var AnnouncementFilter */
        $filter = new UserFilter();
        $filter->setProfile($this->createProfile());

        /** @var array */
        $users = $this->repository->findByFilter($filter);
        $count = $this->repository->countByFilter($filter);

        $this->assertNotNull($users);
        $this->assertEquals(count($users), $count);

        foreach ($users as $user) {
            $profile = $user["profile"];

            $this->assertEquals($filter->getProfile()->getGender(), $profile["gender"]);
        }
    }


    private function createProfile(): Profile {
        $profile = new Profile();

        $profile->setGender(ProfileConstants::GENDER_MALE);
        $profile->setDiet(ProfileConstants::DIET_MEAT_EATER);

        return $profile;
    }

}