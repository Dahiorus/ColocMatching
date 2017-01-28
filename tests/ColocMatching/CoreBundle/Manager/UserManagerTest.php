<?php

namespace Test\ColocMatching\CoreBundle\Manager;

use PHPUnit\Framework\TestCase;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;

/**
 * Unit tests for UserManagers
 *
 * @author brondon.ung
 */
class UserManagerTest extends TestCase {

    private $userManager;


    public function setUp() {
        $this->userManager = $this->createMock(UserManager::class);
    }


    public function testReadUser() {
        /** @var PHPUnit_Framework_MockObject_MockObject $mockedUser */
        $mockedUser = $this->createMock(User::class);
        
        $mockedUser->expects($this->atLeastOnce())->method("getId")->willReturn(1);
        $mockedUser->expects($this->atLeastOnce())->method("getEmail")->willReturn("phpunit@test.fr");
        
        $this->userManager->expects($this->once())->method("read")->with($mockedUser->getId())->willReturn($mockedUser);
        
        /** @var User $user */
        $user = $this->userManager->read($mockedUser->getId());
        
        $this->assertEquals(1, $user->getId());
        $this->assertEquals($mockedUser->getEmail(), $user->getEmail());
    }


    public function testListUsers() {
        /** @var array */
        $mockedUsers = [ ];
        
        for ($i = 1; $i <= 10; $i++) {
            $mockedUser = $this->createMock(User::class);
            $mockedUser->expects($this->any())->method("getId")->willReturn($i);
            
            $mockedUsers[] = $mockedUser;
        }
        
        $this->userManager->expects($this->once())->method("list")->with($this->isInstanceOf(AbstractFilter::class))->willReturn(
            $mockedUsers);
        $this->userManager->expects($this->once())->method("countAll")->willReturn(count($mockedUsers));
        
        $users = $this->userManager->list(new UserFilter());
        $nbUsers = $this->userManager->countAll();
        
        $this->assertNotEmpty($users);
        $this->assertEquals(count($mockedUsers), $nbUsers);
        
        for ($i = 0; $i < count($users); $i++) {
            $this->assertEquals($users[$i], $mockedUsers[$i]);
        }
    }


    public function testSelectFieldsFromOneUser() {
        /** @var array */
        $fields = [ "id", "email", "lastname", "gender"];
        
        /** @var PHPUnit_Framework_MockObject_MockObject $mockedUser */
        $mockedUser = $this->createMock(User::class);
        
        $mockedUser->expects($this->any())->method("getId")->willReturn(1);
        $mockedUser->expects($this->any())->method("getEmail")->willReturn("phpunit@test.fr");
        $mockedUser->expects($this->any())->method("getLastname")->willReturn("phpunit");
        $mockedUser->expects($this->any())->method("getGender")->willReturn(UserConstants::GENDER_UNKNOWN);
        
        $this->userManager->expects($this->once())->method("read")->with($mockedUser->getId(), $fields)->willReturn(
            array ("id" => $mockedUser->getId(), "email" => $mockedUser->getEmail(),
                "lastname" => $mockedUser->getLastname(), "gender" => $mockedUser->getGender()));
        
        $user = $this->userManager->read(1, $fields);
        
        foreach ($fields as $field) {
            $this->assertArrayHasKey($field, $user);
        }
        
        $this->assertArrayNotHasKey("type", $user);
    }


    public function testCreateUser() {
        /** @var array */
        $data = array ("email" => "phpunit@test.fr", "firstname" => "User firstname", "lastname" => "User lastname",
            "plainPassword" => "password");
        /** @var PHPUnit_Framework_MockObject_MockObject $mockedUser */
        $mockedUser = $this->createMock(User::class);
        
        $mockedUser->expects($this->any())->method("getId")->willReturn(1);
        $mockedUser->expects($this->any())->method("getEmail")->willReturn("phpunit@test.fr");
        $mockedUser->expects($this->any())->method("getFirstname")->willReturn("User firstname");
        $mockedUser->expects($this->any())->method("getLastname")->willReturn("User lastname");
        $mockedUser->expects($this->any())->method("getPlainPassword")->willReturn("password");
        
        $this->userManager->expects($this->once())->method("create")->with($data)->willReturn($mockedUser);
        
        $user = $this->userManager->create($data);
        
        $this->assertNotEmpty($user);
        $this->assertEquals($mockedUser->getId(), $user->getId());
        $this->assertEquals($data["email"], $user->getEmail());
        $this->assertEquals($data["firstname"], $user->getFirstname());
        $this->assertEquals($data["lastname"], $user->getLastname());
        $this->assertEquals($data["plainPassword"], $user->getPlainPassword());
    }

}
