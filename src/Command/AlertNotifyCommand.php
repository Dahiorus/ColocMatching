<?php

namespace App\Command;

use App\Core\DTO\Alert\AlertDto;
use App\Core\DTO\Page;
use App\Core\DTO\User\UserDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\Alert\AlertDtoManagerInterface;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\AnnouncementFilter;
use App\Core\Repository\Filter\GroupFilter;
use App\Core\Repository\Filter\Pageable\Order;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Repository\Filter\Searchable;
use App\Core\Repository\Filter\UserFilter;
use App\Core\Service\MailerService;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line to get enabled alert and notify users of alert search results
 * @author Dahiorus
 */
class AlertNotifyCommand extends Command
{
    const DATE_FORMAT = "Y-m-d";

    protected static $defaultName = "app:alert-notify";

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

    /** @var MailerService */
    private $mailer;

    /** @var \DateTime */
    private $now;


    public function __construct(LoggerInterface $logger, UserDtoManagerInterface $userManager,
        AlertDtoManagerInterface $alertManager, AnnouncementDtoManagerInterface $announcementManager,
        GroupDtoManagerInterface $groupManager, MailerService $mailer)
    {
        parent::__construct(self::$defaultName);

        $this->logger = $logger;
        $this->userManager = $userManager;
        $this->alertManager = $alertManager;
        $this->announcementManager = $announcementManager;
        $this->groupManager = $groupManager;
        $this->mailer = $mailer;
        $this->now = new \DateTime();
    }


    protected function configure()
    {
        $this->setName(static::$defaultName)->setDescription("Notifies alert users with search results");
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try
        {
            /** @var AlertDto[] $alerts */
            $alerts = $this->alertManager->findEnabledAlerts()->getContent();
            $count = 0;

            foreach ($alerts as $alert)
            {
                if ($this->notifyAlert($alert))
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
     * @param AlertDto $alert
     *
     * @return bool
     * @throws ORMException
     * @throws EntityNotFoundException
     */
    private function notifyAlert(AlertDto $alert) : bool
    {
        $datePeriod = new \DatePeriod($alert->getCreatedAt(), $alert->getSearchPeriod(), $this->now);

        /** @var \DateTime $date */
        foreach ($datePeriod as $date)
        {
            /** @var \DateInterval $diff */
            $diff = $this->now->diff($date);

            if ($diff && $diff->d === 0) // test if the date match today
            {
                $results = $this->search($alert);

                if ($results->getCount() > 0)
                {
                    /** @var UserDto $user */
                    $user = $this->userManager->read($alert->getUserId());
                    $this->notifyUser($user, $results, $alert->getNotificationType());

                    return true;
                }
            }
        }

        return false;
    }


    /**
     * @param AlertDto $alert
     *
     * @return Page
     * @throws ORMException
     */
    private function search(AlertDto $alert) : Page
    {
        /** @var Searchable $filter */
        $filter = $alert->getFilter();
        $pageable = new PageRequest(1, 10, array ("createdAt" => Order::DESC));

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


    private function notifyUser(UserDto $user, Page $response, string $notificationType)
    {
        $this->logger->debug("Notifying the user [{user}] by [{type}] with the results [{response}]",
            array ("user" => $user, "response" => $response, "type" => $notificationType));
        // TODO complete the method
    }
}
