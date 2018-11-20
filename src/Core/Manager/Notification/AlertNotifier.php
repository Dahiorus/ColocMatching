<?php

namespace App\Core\Manager\Notification;

use App\Core\DTO\Alert\AlertDto;
use App\Core\DTO\Page;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Alert\NotificationType;
use App\Core\Repository\Filter\AnnouncementFilter;
use App\Core\Repository\Filter\GroupFilter;
use App\Core\Repository\Filter\UserFilter;
use Psr\Log\LoggerInterface;

class AlertNotifier
{
    private const ALERT_MAIL_TEMPLATE = "mail/Alert/alert_%s_results_mail.html.twig";
    private const ALERT_MAIL_SUBJECT_PREFIX = "mail.subject.alert.";

    /** @var LoggerInterface */
    private $logger;

    /** @var MailManager */
    private $mailManager;


    public function __construct(LoggerInterface $logger, MailManager $mailManager)
    {
        $this->logger = $logger;
        $this->mailManager = $mailManager;
    }


    public function notify(UserDto $user, Page $response, AlertDto $alert) : void
    {
        $notificationType = $alert->getNotificationType();
        $entityType = $this->getResponseType($alert);

        $this->logger->debug("Notifying the user [{user}] by [{type}] with the results [{response}]",
            array ("user" => $user, "response" => $response, "type" => $notificationType));

        $parameters = array (
            "recipient" => $user,
            "alert" => $alert->getName(),
            "count" => $response->getCount(),
            "results" => $response->getContent()
        );

        switch ($notificationType)
        {
            case NotificationType::EMAIL:
                $this->mailManager->sendEmail($user, self::ALERT_MAIL_SUBJECT_PREFIX . $entityType,
                    sprintf(self::ALERT_MAIL_TEMPLATE, $entityType), ["%alert%" => $alert->getName()], $parameters);
                break;
            case NotificationType::PUSH:
            case NotificationType::SMS:
                $this->logger->warning("Not supported notification type yet");
                break;
            default:
                $this->logger->error("Unsupported notification type [{notificationType}]",
                    array ("notificationType" => $notificationType));
        }
    }


    private function getResponseType(AlertDto $alert) : string
    {
        $filter = $alert->getFilter();

        if ($filter instanceof AnnouncementFilter)
        {
            return "announcement";
        }

        if ($filter instanceof GroupFilter)
        {
            return "group";
        }

        if ($filter instanceof UserFilter)
        {
            return "user";
        }

        throw new \RuntimeException("Unsupported filter type in " . $alert);
    }

}