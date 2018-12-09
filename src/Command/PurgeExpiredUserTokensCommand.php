<?php

namespace App\Command;

use App\Core\Manager\User\UserTokenDtoManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line to delete all expired user tokens
 *
 * @author Dahiorus
 */
class PurgeExpiredUserTokensCommand extends Command
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
        $this->setName(static::$defaultName)->setDescription("Purges all user tokens expired since now");
        $this->addOption("dry-run", null, InputOption::VALUE_NONE, "Execute in simulation mode");
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try
        {
            $now = new \DateTimeImmutable();
        }
        catch (\Exception $e)
        {
            $this->logger->error("Unexpected error while getting a date", array ("exception" => $e));

            return 1;
        }

        $dateAsString = $now->format(DATE_ISO8601);

        $this->logger->debug("Executing {command} at {date}...",
            array ("command" => $this->getName(), "date" => $dateAsString));

        if ($input->getOption("dry-run"))
        {
            try
            {
                $count = $this->userTokenManager->countAllBefore($now);
            }
            catch (ORMException $e)
            {
                $this->logger->error("Cannot count the user tokens to delete", array ("exception" => $e));

                return 1;
            }

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

}