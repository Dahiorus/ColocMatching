<?php

namespace ColocMatching\CoreBundle\Tests\Command;

use ColocMatching\CoreBundle\Command\CreateAdminCommand;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CreateAdminCommandTest extends KernelTestCase
{
    /** @var CommandTester */
    private $commandTester;

    /** @var Application */
    private $application;

    /** @var Command */
    private $command;

    /** @var UserDtoManagerInterface */
    private $userManager;


    public static function setUpBeforeClass()
    {
        self::bootKernel();
    }


    public static function tearDownAfterClass()
    {
        self::ensureKernelShutdown();
    }


    protected function setUp()
    {
        $this->userManager = static::$kernel->getContainer()->get("coloc_matching.core.user_dto_manager");

        $this->application = new Application(static::$kernel);
        $this->application->add(new CreateAdminCommand($this->userManager));

        $this->command = $this->application->find("app:create-admin");
        $this->commandTester = new CommandTester($this->command);

        $this->userManager->deleteAll();
    }


    protected function tearDown()
    {
        $this->userManager->deleteAll();
    }


    /**
     * @throws \Exception
     */
    public function testExecute()
    {
        $data = array ("email" => "admin@coloc-matching.com",
            "password" => "secret123");

        $this->commandTester->execute(array_merge(array ("command" => $this->command->getName()), $data));

        $user = $this->userManager->findByUsername($data["email"]);
        self::assertNotEmpty($user, "Expected admin user to be created");
        self::assertEquals($data["email"], $user->getUsername());

        $output = $this->commandTester->getDisplay();
        self::assertContains("Admin user '" . $data["email"] . "' created", $output,
            "Expected success message to be displayed");
    }


    /**
     * @throws \Exception
     */
    public function testExecuteWithInvalidPassword()
    {
        $data = array ("email" => "admin@coloc-matching.com",
            "password" => "short");

        $this->commandTester->execute(array_merge(array ("command" => $this->command->getName()), $data));

        $this->expectException(EntityNotFoundException::class);
        $this->userManager->findByUsername($data["email"]);

        $output = $this->commandTester->getDisplay();
        self::assertContains("Invalid form data", $output, "Expected validation error");
    }


    /**
     * @throws \Exception
     */
    public function testExecuteToCreateAdminWithNonUniqueEmail()
    {
        $data = array ("email" => "admin@coloc-matching.com",
            "password" => "password");

        $this->userManager->create(
            array ("email" => $data["email"], "plainPassword" => $data["password"], "firstName" => "Admin",
                "lastName" => "Admin", "type" => UserConstants::TYPE_SEARCH));

        $this->commandTester->execute(array_merge(array ("command" => $this->command->getName()), $data));
        $output = $this->commandTester->getDisplay();
        self::assertContains("Invalid form data", $output, "Expected validation error");
    }
}
