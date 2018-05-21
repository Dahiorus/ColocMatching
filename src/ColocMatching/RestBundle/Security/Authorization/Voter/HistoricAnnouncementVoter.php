<?php

namespace ColocMatching\RestBundle\Security\Authorization\Voter;

use ColocMatching\CoreBundle\DTO\Announcement\HistoricAnnouncementDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Service\RoleService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class HistoricAnnouncementVoter extends Voter
{
    const GET = "historic_announcement.get";

    /** @var LoggerInterface */
    private $logger;

    /** @var RoleService */
    private $roleService;


    public function __construct(LoggerInterface $logger, RoleService $roleService)
    {
        $this->logger = $logger;
        $this->roleService = $roleService;
    }


    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array (self::GET)))
        {
            return false;
        }

        if (!($subject instanceof HistoricAnnouncementDto))
        {
            return false;
        }

        return true;
    }


    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        /** @var HistoricAnnouncementDto $announcement */
        $announcement = $subject;

        $this->logger->debug("Evaluating access to '$attribute'", array ("user" => $user, "subject" => $subject));

        if (!($user instanceof UserInterface))
        {
            return false;
        }

        switch ($attribute)
        {
            case self::GET:
                $result = $announcement->getCreatorId() == $user->getId()
                    || $this->roleService->isGranted("ROLE_ADMIN", $user);
                break;
            default:
                $result = false;
                break;
        }

        $this->logger->debug("'$attribute' evaluation result",
            array ("user" => $user, "subject" => $subject, "result" => $result));

        return $result;
    }

}
