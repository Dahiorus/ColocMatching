<?php

namespace ColocMatching\RestBundle\Security\Authorization\Voter;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter to granted access to services on an announcement
 *
 * @author Dahiorus
 */
class AnnouncementVoter extends Voter
{
    const CREATE = "create";
    const UPDATE = "update";
    const DELETE = "delete";
    const REMOVE_CANDIDATE = "remove_candidate";
    const COMMENT = "comment";

    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;


    public function __construct(AnnouncementDtoManagerInterface $announcementManager)
    {
        $this->announcementManager = $announcementManager;
    }


    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array (self::UPDATE, self::REMOVE_CANDIDATE)))
        {
            return false;
        }

        if (!($subject instanceof AnnouncementDto))
        {
            return false;
        }

        return true;
    }


    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        /** @var AnnouncementDto $announcement */
        $announcement = $subject;

        if (!($user instanceof UserInterface))
        {
            return false;
        }

        switch ($attribute)
        {
            case self::UPDATE:
            case self::DELETE:
            case self::CREATE:
                $result = $this->isCreator($user, $announcement);
                break;
            case self::REMOVE_CANDIDATE:
                $result = $this->isCandidate($user, $announcement)
                    || $this->isCreator($user, $announcement);
                break;
            case self::COMMENT:
                $result = $this->isCandidate($user, $announcement);
                break;
            default:
                $result = false;
                break;
        }

        return $result;
    }


    private function isCreator(User $user, AnnouncementDto $announcement)
    {
        return $announcement->getCreatorId() == $user->getId();
    }


    private function isCandidate(User $user, AnnouncementDto $announcement)
    {
        try
        {
            $candidates = $this->announcementManager->getCandidates($announcement);
            $isCandidate = !empty(array_filter($candidates, function (User $c) use ($user) {
                return $c->getId() == $user->getId();
            }));

            return $isCandidate;
        }
        catch (ORMException $e)
        {
            return false;
        }
    }

}