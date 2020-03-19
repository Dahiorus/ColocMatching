<?php

namespace App\Tests\Command;

use App\Command\PurgeExpiredUserTokensCommand;
use App\Core\Entity\User\UserToken;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Manager\User\UserTokenDtoManagerInterface;
use App\Tests\CreateUserTrait;
use Symfony\Component\Console\Output\OutputInterface;

class PurgeExpiredUserTokensCommandTest extends AbstractCommandTest
{
    use CreateUserTrait;

    /** @var UserTokenDtoManagerInterface */
    private $userTokenManager;

    /** @var UserDtoManagerInterface */
    private $userManager;


    protected function getCommandName() : string
    {
        return PurgeExpiredUserTokensCommand::getDefaultName();
    }


    protected function initServices() : void
    {
        $this->userManager = $this->getService("coloc_matching.core.user_dto_manager");
        $this->userTokenManager = $this->getService("coloc_matching.core.user_token_dto_manager");
    }


    protected function initTestData() : void
    {
        for ($i = 0; $i < 5; $i++)
        {
            $user = $this->createSearchUser($this->userManager, "user-$i@yopmail.com");
            $this->userTokenManager->createOrUpdate($user, UserToken::LOST_PASSWORD,
                new \DateTime("-$i days"));
        }
    }


    protected function destroyData() : void
    {
        $this->userTokenManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @test
     */
    public function execute()
    {
        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        self::assertContains("Deleted 5 user tokens expired since", $output,
            "Expected success message to be displayed");
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
        self::assertRegExp("/5 user tokens expired since .* should be deleted/", $output,
            "Expected success message to be displayed");
    }

}
