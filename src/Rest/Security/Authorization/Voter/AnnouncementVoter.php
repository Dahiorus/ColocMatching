<?php

namespace App\Rest\Security\Authorization\Voter;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\Announcement\CommentDto;
use App\Core\DTO\Collection;
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

        if (is_array($subject))
        {
            // must have the announcement and the userId
            return (!empty($subject["announcement"]) && ($subject["announcement"] instanceof AnnouncementDto))
                && (isset($subject["targetId"]) && !is_null($subject["targetId"]) && is_int($subject["targetId"]));
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
        $announcement = is_array($subject) ? $subject["announcement"] : $subject;

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
                $result = ($this->isCandidate($user, $announcement) && $user->getId() == $subject["targetId"])
                    || $this->isCreator($user, $announcement);
                break;
            case self::COMMENT:
                $result = $this->isCandidate($user, $announcement);
                break;
            case self::DELETE_COMMENT:
                $result = $this->isCreator($user, $announcement) ||
                    ($this->isCandidate($user, $announcement)
                        && $this->isCommentAuthor($user, $announcement, $subject["targetId"]));
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
            $this->logger->error("Unable to know if the user [{user}] is a candidate of [{announcement}]",
                array ("user" => $user, "announcement" => $announcement, "exception" => $e));

            return false;
        }
    }


    private function isCommentAuthor(User $user, AnnouncementDto $announcement, int $commentId) : bool
    {
        try
        {
            /** @var Collection<CommentDto> $comments */
            $comments = $this->announcementManager->getComments($announcement);
        }
        catch (ORMException | EntityNotFoundException $e)
        {
            $this->logger->error("Unable to get the announcement [{announcement}] comments",
                array ("announcement" => $announcement, "exception" => $e));

            return false;
        }

        /** @var CommentDto[] $filteredComments */
        $filteredComments = array_filter($comments->getContent(), function (CommentDto $comment) use ($commentId) {
            return $commentId == $comment->getId();
        });

        if (empty($filteredComments))
        {
            return true; // anyone can "delete" a NULL comment
        }

        return $filteredComments[0]->getAuthorId() == $user->getId();
    }

}