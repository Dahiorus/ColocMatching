<?php

namespace App\Command;

use App\Core\Manager\User\UserTokenDtoManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line to delete all expired user tokens
 *
 * @author Dahiorus
 */
class PurgeExpiredUserTokensCommand extends CommandWithDryRun
{
    protected static $defaultName = "app:purge-expired-user-tokens";

    /** @var LoggerInterface */
    private $logger;

    /** @var UserTokenDtoManagerInterface */
    private $userTokenManager;


    public function __construct(LoggerInterface $logger, UserTokenDtoManagerInterface $userTokenManager)
    {
        parent::__construct(self::$defaultName);

        $this->logger = $logger;
        $this->userTokenManager = $userTokenManager;
    }


    protected function configure()
    {
        $this->setDescription("Purges all user tokens expired since now");
        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try
        {
            $now = new \DateTimeImmutable();
            $dateAsString = $now->format(DATE_ISO8601);

            $this->logger->debug("Executing {command} at {date}...",
                array ("command" => $this->getName(), "date" => $dateAsString));

            if ($this->isDryRunEnabled($input))
            {
                $count = $this->userTokenManager->countAllBefore($now);
                $output->writeln(
                    sprintf("%d user tokens expired since %s should be deleted", $count, $dateAsString),
                    OutputInterface::VERBOSITY_VERBOSE);
            }
            else
            {
                $count = $this->userTokenManager->deleteAllBefore($now);
                $output->writeln(sprintf("Deleted %d user tokens expired since %s", $count, $dateAsString));
            }

            return 0;
        }
        catch (\Exception $e)
        {
            $this->logger->error("Unexpected error while running the command {command}",
                array ("command" => $this->getName(), "exception" => $e));

            return 1;
        }
    }

}
