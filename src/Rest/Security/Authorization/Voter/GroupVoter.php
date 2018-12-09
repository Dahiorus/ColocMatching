<?php

namespace App\Rest\Security\Authorization\Voter;

use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\User;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter to grant access to services on a group
 *
 * @author Dahiorus
 */
class GroupVoter extends Voter
{
    use VoterResultLoggerTrait;

    const UPDATE = "group.update";
    const DELETE = "group.delete";
    const REMOVE_MEMBER = "group.remove_member";
    const UPDATE_PICTURE = "group.update_picture";
    const MESSAGE = "group.message";

    /** @var LoggerInterface */
    private $logger;

    /** @var GroupDtoManagerInterface */
    private $groupManager;


    public function __construct(LoggerInterface $logger, GroupDtoManagerInterface $groupManager)
    {
        $this->logger = $logger;
        $this->groupManager = $groupManager;
    }


    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute,
            array (self::UPDATE, self::DELETE, self::REMOVE_MEMBER, self::UPDATE_PICTURE, self::MESSAGE)))
        {
            return false;
        }

        if (is_array($subject))
        {
            // must have the group and the userId
            return (!empty($subject["group"]) && ($subject["group"] instanceof GroupDto))
                && (isset($subject["userId"]) && !is_null($subject["userId"]) && is_int($subject["userId"]));
        }

        if (!($subject instanceof GroupDto))
        {
            return false;
        }

        return true;
    }


    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        /** @var GroupDto $group */
        $group = is_array($subject) ? $subject["group"] : $subject;

        $this->logger->debug("Evaluating access to '$attribute'", array ("user" => $user, "subject" => $subject));

        if (!($user instanceof UserInterface))
        {
            return false;
        }

        switch ($attribute)
        {
            case self::UPDATE:
            case self::DELETE:
            case self::UPDATE_PICTURE:
                $result = $this->isCreator($user, $group);
                break;
            case self::REMOVE_MEMBER:
                $result = $this->isCreator($user, $group)
                    || ($this->isMember($user, $group) && $user->getId() == $subject["userId"]);
                break;
            case self::MESSAGE:
                $result = $this->isMember($user, $group);
                break;
            default:
                $result = false;
                break;
        }

        $this->logResult($this->logger, $result, $attribute, $user, $subject);

        return $result;
    }


    private function isCreator(User $user, GroupDto $group) : bool
    {
        return $group->getCreatorId() == $user->getId();
    }


    private function isMember(User $user, GroupDto $group) : bool
    {
        try
        {
            $userDto = new UserDto();
            $userDto->setId($user->getId());

            return $this->groupManager->hasMember($group, $userDto);
        }
        catch (EntityNotFoundException | ORMException $e)
        {
            $this->logger->error("Unexpected exception while testing if [{user}] is a member of [{group}]",
                array ("user" => $user, "group" => $group, "exception" => $e));

            return false;
        }
    }

}
