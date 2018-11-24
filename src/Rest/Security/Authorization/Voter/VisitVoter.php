<?php

namespace App\Rest\Security\Authorization\Voter;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\User\UserDto;
use App\Core\DTO\Visit\VisitableDto;
use App\Core\Entity\User\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class VisitVoter extends Voter
{
    use VoterResultLoggerTrait;

    const VIEW = "visit.view";

    /** @var LoggerInterface */
    private $logger;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array (self::VIEW)))
        {
            return false;
        }

        return ($subject instanceof VisitableDto);
    }


    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        /** @var VisitableDto $visited */
        $visited = $subject;

        $this->logger->debug("Evaluating access to '$attribute'", array ("user" => $user, "subject" => $subject));

        if (!($user instanceof UserInterface))
        {
            $result = false;
        }
        else if ($visited instanceof UserDto)
        {
            $result = $user->getId() == $visited->getId();
        }
        else if ($visited instanceof AnnouncementDto)
        {
            $result = $user->getId() == $visited->getCreatorId();
        }

        else if ($visited instanceof GroupDto)
        {
            $result = $user->getId() == $visited->getCreatorId();
        }
        else
        {
            $result = false;
        }

        $this->logResult($this->logger, $result, $attribute, $user, $subject);

        return $result;
    }

}