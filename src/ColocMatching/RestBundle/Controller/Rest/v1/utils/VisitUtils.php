<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\utils;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\VisitableDto;
use ColocMatching\CoreBundle\Manager\Visit\AnnouncementVisitDtoManager;
use ColocMatching\CoreBundle\Manager\Visit\GroupVisitDtoManager;
use ColocMatching\CoreBundle\Manager\Visit\UserVisitDtoManager;
use ColocMatching\CoreBundle\Manager\Visit\VisitDtoManagerInterface;

class VisitUtils
{
    private const USER = "user";
    private const ANNOUNCEMENT = "announcement";
    private const GROUP = "group";

    /**
     * @var AnnouncementVisitDtoManager
     */
    private $announcementVisitManager;

    /**
     * @var GroupVisitDtoManager
     */
    private $groupVisitManager;

    /**
     * @var UserVisitDtoManager
     */
    private $userVisitManager;


    public function __construct(AnnouncementVisitDtoManager $announcementVisitManager,
        GroupVisitDtoManager $groupVisitManager, UserVisitDtoManager $userVisitManager)
    {
        $this->announcementVisitManager = $announcementVisitManager;
        $this->groupVisitManager = $groupVisitManager;
        $this->userVisitManager = $userVisitManager;
    }


    /**
     * Gets the visit CRUD manager corresponding to the visitable type
     *
     * @param string $visitableType The visitable type from which get the manager
     *
     * @return VisitDtoManagerInterface
     * @throws \Exception
     */
    public function getManager(string $visitableType) : VisitDtoManagerInterface
    {
        $manager = null;

        switch ($visitableType)
        {
            case self::USER:
                $manager = $this->userVisitManager;
                break;
            case self::ANNOUNCEMENT:
                $manager = $this->announcementVisitManager;
                break;
            case self::GROUP:
                $manager = $this->groupVisitManager;
                break;
            default:
                throw new \InvalidArgumentException("Unknown visitable type");
                break;
        }

        return $manager;
    }


    /**
     * Indicates if the user can access to the visits of the visitable.
     * For a visitable of type user, the user must be the authenticated user.
     * For a visitable of type announcement or group, the user must be its creator.
     *
     * @param UserDto $user The user who wants to access to the visits
     * @param VisitableDto $visitable The visitable
     *
     * @return bool
     */
    public function isAuthorized(UserDto $user, VisitableDto $visitable) : bool
    {
        if ($visitable instanceof UserDto)
        {
            return $user->getId() == $visitable->getId();
        }

        if ($visitable instanceof AnnouncementDto)
        {
            return $user->getId() == $visitable->getCreatorId();
        }

        if ($visitable instanceof GroupDto)
        {
            return $user->getId() == $visitable->getCreatorId();
        }

        return false;
    }
}