<?php

namespace App\Tests\Command;

use App\Command\DeleteUsersCommand;
use App\Core\Entity\User\DeleteUserEvent;
use App\Core\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteUsersCommandTest extends AbstractCommandTest
{
    /** @var EntityManagerInterface */
    private $entityManager;


    protected function getCommandName() : string
    {
        return DeleteUsersCommand::getDefaultName();
    }


    protected function initServices() : void
    {
        $this->entityManager = $this->getService("doctrine.orm.entity_manager");
    }


    protected function initTestData() : void
    {
        foreach ([1, 2, 3, 4, 5] as $id)
        {
            // create a user
            $user = new User("user-$id@yopmail.com", "secret123", "User-$id", "Test");
            $this->entityManager->persist($user);

            // create a delete user event
            $event = new DeleteUserEvent($user);
            $event->setDeleteAt(new \DateTimeImmutable());
            $this->entityManager->persist($event);
        }

        $this->entityManager->flush();
    }


    protected function destroyData() : void
    {
        $this->getService("coloc_matching.core.user_dto_manager")->deleteAll();
    }


    /**
     * @test
     * @throws \Exception
     */
    public function execute()
    {
        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        self::assertContains("Deleted 5 users at", $output, "Expected success message to be displayed");
    }


    /**
     * @test
     */
    public function executeWithDryRun()
    {
        $this->commandTester->execute(
            array ("-vv" => true, "--dry-run" => true),
            array ("verbosity" => OutputInterface::VERBOSITY_VERBOSE));

        $output = $this->commandTester->getDisplay();
        self::assertRegExp("/5 users should be deleted at .+/", $output,
            "Expected dry-run message to be displayed");
    }

}
