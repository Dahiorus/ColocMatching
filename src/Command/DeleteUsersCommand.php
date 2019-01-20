<?php

namespace App\Command;

use App\Core\DTO\Collection;
use App\Core\DTO\User\UserDto;
use App\Core\Manager\User\UserDtoManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line to delete all users having a delete event at the command execution date
 *
 * @author Dahiorus
 */
class DeleteUsersCommand extends CommandWithDryRun
{
    protected static $defaultName = "app:delete-users";

    /** @var LoggerInterface */
    private $logger;

    /** @var UserDtoManagerInterface */
    private $userManager;


    public function __construct(LoggerInterface $logger, UserDtoManagerInterface $userManager)
    {
        parent::__construct();
        $this->logger = $logger;
        $this->userManager = $userManager;
    }


    protected function configure()
    {
        $this->setDescription("Deletes all users having a delete event set for today");
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

            /** @var Collection $users */
            $users = $this->userManager->getUsersToDeleteAt($now);

            if ($this->isDryRunEnabled($input))
            {
                $output->writeln(
                    sprintf("%d users should be deleted at %s", $users->getCount(), $dateAsString),
                    OutputInterface::VERBOSITY_VERBOSE);

                $content = $users->getContent();
                array_walk($content, function (UserDto $user) {
                    $this->logger->debug("[{user}] will be deleted", ["user" => $user]);
                });
            }
            else
            {
                $this->userManager->deleteList($users->getContent());

                $output->writeln(sprintf("Deleted %d users at %s", $users->getCount(), $dateAsString));
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
