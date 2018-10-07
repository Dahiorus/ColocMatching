<?php

namespace App\Rest\Security\Authorization\Voter;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\User;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter to grant access to services on an announcement
 *
 * @author Dahiorus
 */
class AnnouncementVoter extends Voter
{
    use VoterResultLoggerTrait;

    const UPDATE = "announcement.update";
    const DELETE = "announcement.delete";
    const REMOVE_CANDIDATE = "announcement.remove_candidate";
    const ADD_PICTURE = "announcement.add_picture";
    const COMMENT = "announcement.comment";
    const DELETE_COMMENT = "announcement.delete_comment";

    /** @var LoggerInterface */
    private $logger;

    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;


    public function __construct(LoggerInterface $logger, AnnouncementDtoManagerInterface $announcementManager)
    {
        $this->logger = $logger;
        $this->announcementManager = $announcementManager;
    }


    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute,
            array (self::UPDATE, self::DELETE, self::REMOVE_CANDIDATE, self::ADD_PICTURE,
                self::COMMENT, self::DELETE_COMMENT)))
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

        $this->logger->debug("Evaluating access to '$attribute'", array ("user" => $user, "subject" => $subject));

        if (!($user instanceof UserInterface))
        {
            return false;
        }

        switch ($attribute)
        {
            case self::UPDATE:
            case self::DELETE:
            case self::ADD_PICTURE:
                $result = $this->isCreator($user, $announcement);
                break;
            case self::REMOVE_CANDIDATE:
                $result = $this->isCandidate($user, $announcement)
                    || $this->isCreator($user, $announcement);
                break;
            case self::COMMENT:
                $result = $this->isCandidate($user, $announcement);
                break;
            case self::DELETE_COMMENT:
                $result = $this->isCandidate($user, $announcement)
                    || $this->isCreator($user, $announcement);
                break;
            default:
                $result = false;
                break;
        }

        $this->logResult($this->logger, $result, $attribute, $user, $subject);

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
            $userDto = new UserDto();
            $userDto->setId($user->getId());

            return $this->announcementManager->hasCandidate($announcement, $userDto);
        }
        catch (EntityNotFoundException | ORMException $e)
        {
            return false;
        }
    }

}