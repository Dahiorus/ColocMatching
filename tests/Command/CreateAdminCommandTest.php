<?php

namespace App\Tests\Command;

use App\Command\CreateAdminCommand;
use App\Core\Entity\User\UserType;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Form\Type\User\RegistrationForm;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\AbstractServiceTest;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CreateAdminCommandTest extends AbstractServiceTest
{
    /** @var CommandTester */
    private $commandTester;

    /** @var Application */
    private $application;

    /** @var Command */
    private $command;

    /** @var UserDtoManagerInterface */
    private $userManager;


    /**
     * @before
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->userManager = $this->getService("coloc_matching.core.user_dto_manager");

        $this->application = new Application(static::$kernel);
        $this->application->add(new CreateAdminCommand($this->userManager));

        $this->command = $this->application->find("app:create-admin");
        $this->commandTester = new CommandTester($this->command);

        $this->userManager->deleteAll();
    }


    protected function tearDown()
    {
        $this->userManager->deleteAll();
        parent::tearDown();
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
        $data = array ("email" => "admin@coloc-matching.com", "password" => "short");

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
        $data = array (
            "email" => "admin@coloc-matching.com",
            "password" => "password"
        );

        $this->userManager->create(
            array (
                "email" => $data["email"],
                "plainPassword" => array (
                    "password" => "secret1234",
                    "confirmPassword" => "secret1234"
                ),
                "firstName" => "Admin",
                "lastName" => "Admin", "type" => UserType::SEARCH),
            RegistrationForm::class
        );

        $this->commandTester->execute(array_merge(array ("command" => $this->command->getName()), $data));
        $output = $this->commandTester->getDisplay();
        self::assertContains("Invalid form data", $output, "Expected validation error");
    }
}
