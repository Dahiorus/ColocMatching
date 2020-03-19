<?php

namespace App\Rest\Security\Authorization\Voter;

use App\Core\DTO\User\UserDto;
use App\Core\Service\RoleService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    use VoterResultLoggerTrait;

    const PREFERENCE_GET = "user.preference.get";
    const PREFERENCE_UPDATE = "user.preference.update";

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RoleService
     */
    private $roleService;


    public function __construct(LoggerInterface $logger, RoleService $roleService)
    {
        $this->logger = $logger;
        $this->roleService = $roleService;
    }


    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array (self::PREFERENCE_GET, self::PREFERENCE_UPDATE)))
        {
            return false;
        }

        if (!($subject instanceof UserDto))
        {
            return false;
        }

        return true;
    }


    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var UserDto $user */
        $user = $token->getUser();
        /** @var UserDto $target */
        $target = $subject;

        $this->logger->debug("Evaluating access to '$attribute'", array ("user" => $user, "subject" => $subject));

        if (!($user instanceof UserInterface))
        {
            return false;
        }

        switch ($attribute)
        {
            case self::PREFERENCE_GET:
            case self::PREFERENCE_UPDATE:
                $result = ($target->getId() == $user->getId()) || $this->roleService->isGranted("ROLE_ADMIN", $user);
                break;
            default:
                $result = false;
                break;
        }

        $this->logResult($this->logger, $result, $attribute, $user, $subject);

        return $result;
    }

}
