<?php

namespace ColocMatching\RestBundle\Security\Authorization\Voter;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\VisitableDto;
use ColocMatching\CoreBundle\Entity\User\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class VisitVoter extends Voter
{
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
            return false;
        }

        if ($visited instanceof UserDto)
        {
            return $user->getId() == $visited->getId();
        }

        if ($visited instanceof AnnouncementDto)
        {
            return $user->getId() == $visited->getCreatorId();
        }

        if ($visited instanceof GroupDto)
        {
            return $user->getId() == $visited->getCreatorId();
        }

        return false;
    }

}