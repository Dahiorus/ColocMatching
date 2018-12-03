<?php

namespace App\Tests\Command;

use App\Command\CreateAdminCommand;
use App\Core\Entity\User\UserStatus;
use App\Core\Entity\User\UserType;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Form\Type\User\RegistrationForm;
use App\Core\Manager\User\UserDtoManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAdminCommandTest extends AbstractCommandTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;


    protected function getCommandName() : string
    {
        return CreateAdminCommand::getDefaultName();
    }


    protected function initServices() : void
    {
        $this->userManager = $this->getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        // empty method
    }


    protected function destroyData() : void
    {
        $this->userManager->deleteAll();
    }


    /**
     * @test
     * @throws \Exception
     */
    public function execute()
    {
        $data = array ("email" => "admin@coloc-matching.com",
            "password" => "secret123");

        $this->commandTester->execute($data);

        $user = $this->userManager->findByUsername($data["email"]);
        self::assertNotEmpty($user, "Expected admin user to be created");
        self::assertEquals($data["email"], $user->getUsername());

        $output = $this->commandTester->getDisplay();
        self::assertContains("Admin user '" . $data["email"] . "' created", $output,
            "Expected success message to be displayed");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function executeWithInvalidPassword()
    {
        $data = array ("email" => "admin@coloc-matching.com", "password" => "short");

        $this->commandTester->execute($data);

        $this->expectException(EntityNotFoundException::class);
        $this->userManager->findByUsername($data["email"]);

        $output = $this->commandTester->getDisplay();
        self::assertContains("Invalid form data", $output, "Expected validation error");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function executeToCreateAdminWithNonUniqueEmail()
    {
        $data = array (
            "email" => "admin@coloc-matching.com",
            "password" => "password"
        );

        $this->userManager->create(
            array (
                "email" => $data["email"],
                "plainPassword" => "secret1234",
                "firstName" => "Admin",
                "lastName" => "Admin", "type" => UserType::SEARCH),
            RegistrationForm::class
        );

        $this->commandTester->execute($data);
        $output = $this->commandTester->getDisplay();
        self::assertContains("Invalid form data", $output, "Expected validation error");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function executeToCreateEnabledAdmin()
    {
        $data = array (
            "email" => "admin@coloc-matching.com",
            "password" => "secret123",
            "--enabled" => true);

        $this->commandTester->execute($data);

        $user = $this->userManager->findByUsername($data["email"]);
        self::assertNotEmpty($user, "Expected admin user to be created");
        self::assertEquals($data["email"], $user->getUsername());
        self::assertTrue($user->getStatus() == UserStatus::ENABLED, "Expected admin to be enabled");

        $output = $this->commandTester->getDisplay();
        self::assertContains("Admin user '" . $data["email"] . "' created", $output,
            "Expected success message to be displayed");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function executeToCreateSuperAdmin()
    {
        $data = array (
            "email" => "admin@coloc-matching.com",
            "password" => "secret123",
            "--super-admin" => true);

        $this->commandTester->execute($data);

        $user = $this->userManager->findByUsername($data["email"]);
        self::assertNotEmpty($user, "Expected admin user to be created");
        self::assertEquals($data["email"], $user->getUsername());
        self::assertContains("ROLE_SUPER_ADMIN", $user->getRoles(), "Expected admin to be super admin");

        $output = $this->commandTester->getDisplay();
        self::assertContains("Admin user '" . $data["email"] . "' created", $output,
            "Expected success message to be displayed");
    }


    /**
     * @test
     */
    public function executeWithDryRun()
    {
        $data = array (
            "email" => "admin@coloc-matching.com",
            "password" => "secret123",
            "--dry-run" => true,
            "-vv" => true);

        $this->commandTester->execute($data, array ("verbosity" => OutputInterface::VERBOSITY_VERBOSE));

        $output = $this->commandTester->getDisplay();
        self::assertRegExp("/Admin user \[.+\] should be created/", $output,
            "Expected dry-run message to be displayed");
    }


    /**
     * @test
     */
    public function interact()
    {

        $this->commandTester->setInputs(array ("admin@coloc-matching.com", "secret123"));
        $this->commandTester->execute([], array ("interactive" => true));

        $output = $this->commandTester->getDisplay();
        self::assertContains("Choose an e-mail address for the admin user", $output,
            "Expected e-mail question to be output");
        self::assertContains("Choose a password for the admin user (min length: 8)", $output,
            "Expected password question to be output");
    }


    /**
     * @test
     */
    public function interactWithInvalidData()
    {

        $this->commandTester->setInputs(array ("", "admin@coloc-matching.com", "", "secret123"));
        $this->commandTester->execute([], array (
            "interactive" => true,
            "capture_stderr_separately" => true
        ));

        $output = $this->commandTester->getErrorOutput();
        self::assertContains("The e-mail address cannot be empty", $output,
            "Expected error message on e-mail");
        self::assertContains("The password cannot be empty", $output,
            "Expected error message on password");
    }

}
