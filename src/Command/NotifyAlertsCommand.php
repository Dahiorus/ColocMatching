<?php

namespace App\Command;

use App\Core\DTO\Alert\AlertDto;
use App\Core\DTO\Page;
use App\Core\DTO\User\UserDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\Alert\AlertDtoManagerInterface;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\Notification\AlertNotifier;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\AnnouncementFilter;
use App\Core\Repository\Filter\GroupFilter;
use App\Core\Repository\Filter\Pageable\Order;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Repository\Filter\Searchable;
use App\Core\Repository\Filter\UserFilter;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line to get enabled alert and notify users of alert search results
 * @author Dahiorus
 */
class NotifyAlertsCommand extends Command
{
    protected static $defaultName = "app:notify-alerts";

    /** @var LoggerInterface */
    private $logger;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var AlertDtoManagerInterface */
    private $alertManager;

    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var GroupDtoManagerInterface */
    private $groupManager;

    /** @var AlertNotifier */
    private $alertNotifier;


    public function __construct(LoggerInterface $logger, UserDtoManagerInterface $userManager,
        AlertDtoManagerInterface $alertManager, AnnouncementDtoManagerInterface $announcementManager,
        GroupDtoManagerInterface $groupManager, AlertNotifier $alertNotifier)
    {
        parent::__construct(self::$defaultName);

        $this->logger = $logger;
        $this->userManager = $userManager;
        $this->alertManager = $alertManager;
        $this->announcementManager = $announcementManager;
        $this->groupManager = $groupManager;
        $this->alertNotifier = $alertNotifier;
    }


    protected function configure()
    {
        $this->setName(static::$defaultName)->setDescription("Notifies alert users with search results");
        $this->addOption("dry-run", null, InputOption::VALUE_NONE, "Execute in simulation mode");
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = new \DateTime();

        $this->logger->debug("Executing {command} at {date}...",
            array ("command" => $this->getName(), "date" => $now->format(DATE_ISO8601)));

        try
        {
            /** @var AlertDto[] $alerts */
            $alerts = $this->alertManager->findEnabledAlerts()->getContent();
            $count = 0;

            foreach ($alerts as $alert)
            {
                if ($input->getOption("dry-run") == true)
                {
                    $output->writeln("Alert [$alert] should be notified", OutputInterface::VERBOSITY_VERBOSE);
                }
                else if ($this->notifyAlert($alert, $now))
                {
                    $this->logger->debug("Alert [{alert}] notified", array ("alert" => $alert));

                    $count++;
                }
            }

            $output->writeln("$count alerts notified");

            return 0;
        }
        catch (\Exception $e)
        {
            $this->logger->error("Unexpected error [{exception}] while running the command {command}",
                array ("exception" => $e, "command" => $this->getName()));

            return 1;
        }
    }


    /**
     * Searches entities from the alert and notifies the alert user with the result
     *
     * @param AlertDto $alert The alert
     * @param \DateTime $now The date to compare to known if the search can be done for the alert
     *
     * @return bool true if the notification is sent, false otherwise
     * @throws ORMException
     * @throws EntityNotFoundException
     */
    private function notifyAlert(AlertDto $alert, \DateTime $now) : bool
    {
        $datePeriod = new \DatePeriod($alert->getCreatedAt(), $alert->getSearchPeriod(), $now);

        /** @var \DateTime $date */
        foreach ($datePeriod as $date)
        {
            /** @var \DateInterval $diff */
            $diff = $now->diff($date);

            if (!empty($diff) && $diff->d === 0) // test if the date match today
            {
                $results = $this->search($alert);

                if ($results->getCount() > 0)
                {
                    /** @var UserDto $user */
                    $user = $this->userManager->read($alert->getUserId());
                    $this->alertNotifier->notify($user, $results, $alert);

                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Searches the entities from the alert
     *
     * @param AlertDto $alert The alert
     *
     * @return Page
     * @throws ORMException
     */
    private function search(AlertDto $alert) : Page
    {
        /** @var Searchable $filter */
        $filter = $alert->getFilter();
        $pageable = new PageRequest(1, $alert->getResultSize(), array ("createdAt" => Order::DESC));

        if ($filter instanceof AnnouncementFilter)
        {
            $this->logger->debug("Searching announcements using the alert [{alert}] filter", array ("alert" => $alert));

            return $this->announcementManager->search($filter, $pageable);
        }

        if ($filter instanceof GroupFilter)
        {
            $this->logger->debug("Searching groups using the alert [{alert}] filter", array ("alert" => $alert));

            return $this->groupManager->search($filter, $pageable);
        }

        if ($filter instanceof UserFilter)
        {
            $this->logger->debug("Searching users using the alert [{alert}] filter", array ("alert" => $alert));

            return $this->userManager->search($filter, $pageable);
        }

        throw new \InvalidArgumentException("Unable to do a search with the alert [$alert]");
    }

}
