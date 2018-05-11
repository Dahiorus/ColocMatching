<?php

namespace ColocMatching\RestBundle\Security\Authorization\Voter;

use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Manager\Group\GroupDtoManagerInterface;
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
        $group = $subject;

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
                $result = $this->isCreator($user, $group) || $this->isMember($user, $group);
                break;
            case self::MESSAGE:
                $result = $this->isMember($user, $group);
                break;
            default:
                $result = false;
                break;
        }

        $this->logger->debug("'$attribute' evaluation result",
            array ("user" => $user, "subject" => $subject, "result" => $result));

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
            return false;
        }
    }

}