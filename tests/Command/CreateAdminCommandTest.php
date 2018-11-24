<?php

namespace App\Tests\Command;

use App\Command\CreateAdminCommand;
use App\Core\Entity\User\UserType;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Form\Type\User\RegistrationForm;
use App\Core\Manager\User\UserDtoManagerInterface;

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
     * @throws \Exception
     */
    public function testExecute()
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
     * @throws \Exception
     */
    public function testExecuteWithInvalidPassword()
    {
        $data = array ("email" => "admin@coloc-matching.com", "password" => "short");

        $this->commandTester->execute($data);

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
                "plainPassword" => "secret1234",
                "firstName" => "Admin",
                "lastName" => "Admin", "type" => UserType::SEARCH),
            RegistrationForm::class
        );

        $this->commandTester->execute($data);
        $output = $this->commandTester->getDisplay();
        self::assertContains("Invalid form data", $output, "Expected validation error");
    }
}
