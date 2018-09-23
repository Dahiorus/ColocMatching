<?php

namespace App\Rest\Security\Authorization\Voter;

use App\Core\DTO\Invitation\InvitableDto;
use App\Core\DTO\Invitation\InvitationDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Group\Group;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\User\User;
use App\Core\Entity\User\UserType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter to grant access to services on an invitation
 *
 * @author Dahiorus
 */
class InvitationVoter extends Voter
{
    const LIST = "invitation.list";
    const INVITE = "invitation.invite";
    const ANSWER = "invitation.answer";
    const DELETE = "invitation.delete";

    /** @var LoggerInterface */
    private $logger;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array (self::DELETE, self::ANSWER, self::LIST, self::INVITE)))
        {
            return false;
        }

        if (in_array($attribute, array (self::LIST, self::INVITE)))
        {
            return ($subject instanceof InvitableDto) || ($subject instanceof UserDto);
        }

        if (in_array($attribute, array (self::DELETE, self::ANSWER)))
        {
            return $subject instanceof InvitationDto;
        }

        return false;
    }


    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        $this->logger->debug("Evaluating access to '$attribute'", array ("user" => $user, "subject" => $subject));

        if (!($user instanceof UserInterface))
        {
            return false;
        }

        if (!$user->isEnabled())
        {
            return false;
        }

        switch ($attribute)
        {
            case self::LIST:
                $result = $this->isAllowToList($user, $subject);
                break;
            case self::INVITE:
                $result = $this->isAllowToInvite($user, $subject);
                break;
            case self::ANSWER:
                $result = $this->isForCreator($user, $subject)
                    || $this->isForRecipient($user, $subject);
                break;
            case self::DELETE:
                $result = $this->isUserInInvitation($user, $subject);
                break;
            default:
                $result = false;
        }

        $this->logger->debug("'$attribute' evaluation result",
            array ("user" => $user, "subject" => $subject, "result" => $result));

        return $result;
    }


    /**
     * @param User $user
     * @param UserDto|InvitableDto $subject
     *
     * @return bool
     */
    private function isAllowToList(User $user, $subject) : bool
    {
        if ($subject instanceof InvitableDto)
        {
            if ($user->hasGroup())
            {
                return $user->getGroup()->getId() == $subject->getId();
            }

            if ($user->hasAnnouncement())
            {
                return $user->getAnnouncement()->getId() == $subject->getId();
            }
        }

        if ($subject instanceof UserDto)
        {
            return $user->getId() == $subject->getId();
        }

        return false;
    }


    /**
     * Tests if the user is allow to create an invitation for the subject.
     * A user having an announcement or a group can only invite search type users.
     * An invitable entity can only be invited by search type users
     *
     * @param User $user The user
     * @param UserDto|InvitableDto $subject The invitation subject
     *
     * @return bool
     */
    private function isAllowToInvite(User $user, $subject) : bool
    {
        if ($subject instanceof UserDto)
        {
            return ($user->hasAnnouncement() || $user->hasGroup()) && $subject->getType() == UserType::SEARCH;
        }

        if ($subject instanceof InvitableDto)
        {
            return $user->getType() == UserType::SEARCH;
        }

        return false;
    }


    /**
     * Tests if the invitation is from an invitable entity and the user is the invitation recipient
     *
     * @param User $recipient The user
     * @param InvitationDto $invitation The invitation
     *
     * @return bool
     */
    private function isForRecipient(User $recipient, InvitationDto $invitation) : bool
    {
        if ($invitation->getSourceType() == Invitation::SOURCE_INVITABLE)
        {
            return $invitation->getRecipientId() == $recipient->getId();
        }

        return false;
    }


    /**
     * Tests if the invitation is from a search type user and the user is the invitable entity creator
     *
     * @param User $creator The user
     * @param InvitationDto $invitation The invitation
     *
     * @return bool
     */
    private function isForCreator(User $creator, InvitationDto $invitation) : bool
    {
        if ($invitation->getSourceType() == Invitation::SOURCE_SEARCH)
        {
            if ($invitation->getInvitableClass() == Announcement::class && $creator->hasAnnouncement())
            {
                return $invitation->getInvitableId() == $creator->getAnnouncement()->getId();
            }

            if ($invitation->getInvitableClass() == Group::class && $creator->hasGroup())
            {
                return $invitation->getInvitableId() == $creator->getGroup()->getId();
            }
        }

        return false;
    }


    /**
     * Tests if the user is the recipient or the invitable entity creator of the invitation
     *
     * @param User $user The user
     * @param InvitationDto $invitation The invitation
     *
     * @return bool
     */
    private function isUserInInvitation(User $user, InvitationDto $invitation) : bool
    {
        $isRecipient = $user->getId() == $invitation->getRecipientId();

        if ($user->hasAnnouncement())
        {
            $isCreator = $invitation->getInvitableClass() == Announcement::class
                && $invitation->getInvitableId() == $user->getAnnouncement()->getId();
        }
        else if ($user->hasGroup())
        {
            $isCreator = $invitation->getInvitableClass() == Group::class
                && $invitation->getInvitableId() == $user->getGroup()->getId();
        }
        else
        {
            $isCreator = false;
        }

        return $isRecipient || $isCreator;
    }
}