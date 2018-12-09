<?php

namespace App\Command;

use App\Core\DTO\Collection;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\Invitation\InvitationDtoManagerInterface;
use App\Core\Manager\Notification\InvitationNotifier;
use App\Core\Repository\Filter\InvitationFilter;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Command line to remind users having not answered yet invitations by notifying them
 *
 * @author Dahiorus
 */
class RemindInvitationsCommand extends Command
{
    protected static $defaultName = "app:remind-invitations";

    /** @var LoggerInterface */
    private $logger;

    /** @var InvitationDtoManagerInterface */
    private $invitationManager;

    /** @var InvitationNotifier */
    private $notifier;


    public function __construct(LoggerInterface $logger, InvitationDtoManagerInterface $invitationManager,
        InvitationNotifier $notifier)
    {
        parent::__construct(self::$defaultName);

        $this->logger = $logger;
        $this->invitationManager = $invitationManager;
        $this->notifier = $notifier;
    }


    protected function configure()
    {
        $this->setDescription("Notifies users they have pending invitations");
        $this->addOption("dry-run", null, InputOption::VALUE_NONE, "Execute in simulation mode");
        $this->addArgument("until", InputArgument::REQUIRED, "Date/time string in a valid PHP format");
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->debug("Executing {command}...", array ("command" => $this->getName()));

        try
        {
            $createdAtUtil = new \DateTime($input->getArgument("until"));
        }
        catch (\Exception $e)
        {
            $this->logger->error("Invalid date given to the input 'until'", array ("exception" => $e));

            return 1;
        }

        $filter = new InvitationFilter();
        $filter->setStatus(Invitation::STATUS_WAITING);
        $filter->setCreatedAtUntil($createdAtUtil);

        try
        {
            /** @var Collection $invitations */
            $invitations = $this->invitationManager->search($filter);

            foreach ($invitations as $invitation)
            {
                if ($input->getOption("dry-run") == true)
                {
                    $output->writeln("Notification should be sent for [$invitation]",
                        OutputInterface::VERBOSITY_VERBOSE);
                }
                else
                {
                    $this->notifier->sendInvitationMail($invitation);
                }
            }

            $output->writeln(sprintf("%d invitations created until [%s] notified",
                $invitations->getCount(), $createdAtUtil->format("Y-M-d")));

            return 0;
        }
        catch (ORMException | EntityNotFoundException $e)
        {
            $this->logger->error("Unexpected error while notifying invitations",
                array ("exception" => $e));

            return 1;
        }
    }


    protected function interact(InputInterface $input, OutputInterface $output)
    {
        /** @var Question[] $questions */
        $questions = array ();

        if (!$input->getArgument("until"))
        {
            $question = new Question("Choose a date/string value in a valid format: ");
            $question->setValidator(function ($date) {
                if (empty($date))
                {
                    throw new \Exception("The date is mandatory");
                }

                return $date;
            });
            $questions["until"] = $question;
        }

        foreach ($questions as $name => $question)
        {
            $answer = $this->getHelper("question")->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }

}
