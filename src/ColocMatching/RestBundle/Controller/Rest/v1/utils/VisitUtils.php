<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\utils;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface;

class VisitUtils {

    private const USER = "user";
    private const ANNOUNCEMENT = "announcement";
    private const GROUP = "group";

    /**
     * @var VisitManagerInterface
     */
    private $announcementVisitManager;

    /**
     * @var VisitManagerInterface
     */
    private $groupVisitManager;

    /**
     * @var VisitManagerInterface
     */
    private $userVisitManager;


    public function __construct(VisitManagerInterface $announcementVisitManager,
        VisitManagerInterface $groupVisitManager, VisitManagerInterface $userVisitManager) {

        $this->announcementVisitManager = $announcementVisitManager;
        $this->groupVisitManager = $groupVisitManager;
        $this->userVisitManager = $userVisitManager;
    }


    /**
     * Gets the visit CRUD manager corresponding to the visitable type
     *
     * @param string $visitableType The visitable type from which get the manager
     *
     * @return VisitManagerInterface
     * @throws \Exception
     */
    public function getManager(string $visitableType) : VisitManagerInterface {
        $manager = null;

        switch ($visitableType) {
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
                throw new \Exception("Unknown visitable type");
                break;
        }

        return $manager;
    }


    /**
     * Indicates if the user can access to the visits of the visitable.
     * For a visitable of type user, the user must be the authenticated user.
     * For a visitable of type announcement or group, the user must be its creator.
     *
     * @param User $user           The user who wants to access to the visits
     * @param Visitable $visitable The visitable
     *
     * @return bool
     */
    public function isAuthorized(User $user, Visitable $visitable) : bool {
        if ($visitable instanceof User) {
            return $user === $visitable;
        }

        if ($visitable instanceof Announcement) {
            return $user === $visitable->getCreator();
        }

        if ($visitable instanceof Group) {
            return $user === $visitable->getCreator();
        }
    }
}