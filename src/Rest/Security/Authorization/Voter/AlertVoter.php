<?php

namespace App\Rest\Security\Authorization\Voter;

use App\Core\DTO\Alert\AlertDto;
use App\Core\Entity\User\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter to grant access to services on an alert
 *
 * @author Dahiorus
 */
class AlertVoter extends Voter
{
    use VoterResultLoggerTrait;

    const GET = "alert.get";
    const UPDATE = "alert.update";
    const DELETE = "alert.delete";

    /** @var LoggerInterface */
    private $logger;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array (self::GET, self::UPDATE, self::DELETE)))
        {
            return false;
        }

        if (!($subject instanceof AlertDto))
        {
            return false;
        }

        return true;
    }


    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        /** @var AlertDto $alert */
        $alert = $subject;

        $this->logger->debug("Evaluating access to '$attribute'", array ("user" => $user, "subject" => $subject));

        if (!($user instanceof UserInterface))
        {
            return false;
        }

        switch ($attribute)
        {
            case self::GET:
            case self::UPDATE:
            case self::DELETE:
                $result = $user->getId() == $alert->getUserId();
                break;
            default:
                $result = false;
                break;
        }

        $this->logResult($this->logger, $result, $attribute, $user, $subject);

        return $result;
    }

}
