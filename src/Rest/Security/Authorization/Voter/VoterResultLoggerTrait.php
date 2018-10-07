<?php

namespace App\Rest\Security\Authorization\Voter;

use App\Core\Entity\User\User;
use Psr\Log\LoggerInterface;

trait VoterResultLoggerTrait
{
    /**
     * Logs the result of the access vote
     *
     * @param LoggerInterface $logger The logger
     * @param bool $isGrantedTo The access vote result
     * @param string $attribute The attribute to vote
     * @param User $user The user who wants to access to the attribute
     * @param mixed $subject The attribute subject
     */
    public function logResult(LoggerInterface $logger, bool $isGrantedTo, string $attribute, User $user, $subject)
    {
        if (!$isGrantedTo)
        {
            $logger->warning("Access DENIED to '$attribute' on [$subject] for [$user]",
                array ("user" => $user, "subject" => $subject, "result" => $isGrantedTo));
        }
        else
        {
            $logger->debug("Access GRANTED to '$attribute' on [$subject] for [$user]",
                array ("user" => $user, "subject" => $subject, "result" => $isGrantedTo));
        }
    }
}