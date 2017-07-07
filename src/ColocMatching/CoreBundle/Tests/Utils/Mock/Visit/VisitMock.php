<?php
/**
 * Created by PhpStorm.
 * User: Dahiorus
 * Date: 06/07/2017
 * Time: 22:35
 */

namespace ColocMatching\CoreBundle\Tests\Utils\Mock\Visit;


use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Entity\Visit\Visit;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Group\GroupMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;

class VisitMock {

    public static function createVisit(int $id, Visitable $visited, User $visitor, \DateTime $visitedAt) : Visit {
        $visit = Visit::create($visited, $visitor);

        $visit->setId($id);
        $visit->setVisitedAt($visitedAt);

        return $visit;
    }


    public static function createVisitPage(PageableFilter $filter, int $total, Visitable $visited = null, User
    $visitor = null) :
    array {
        $visits = array ();

        for ($id = 1; $id <= $total; $id++) {
            if (empty($visited)) {
                $visited = self::buildVisitable();
            }

            if (empty($visitor)) {
                $userId= random_int(1, 10);
                $visitor = UserMock::createUser($userId, "user-test-$userId@test.com", "password", "User", "Test",
                ($userId % 2 == 0) ? UserConstants::TYPE_PROPOSAL : UserConstants::TYPE_SEARCH);
            }

            $visits[] = self::createVisit($id, $visited, $visitor, new \DateTime());
        }

        return array_slice($visits, $filter->getOffset(), $filter->getSize(), true);
    }


    private static function buildVisitable() : Visitable {
        $typeNum = random_int(1, 3);
        $id = random_int(1, 50);
        $visitable = null;

        switch ($typeNum) {
            case 1:
                $visitable = AnnouncementMock::createAnnouncement($id, UserMock::createUser($id, "proposal-$id@test.com", "password", "User", "Test",
                    UserConstants::TYPE_PROPOSAL), "Paris 75005", "Announcement test", Announcement::TYPE_RENT,
                    1400, new \DateTime());
                break;
            case 2:
                $visitable = UserMock::createUser($id, "user-$id@test.com", "password", "User", "Test",
                    UserConstants::TYPE_PROPOSAL);
                break;
            case 3:
                $visitable = GroupMock::createGroup($id, UserMock::createUser($id, "search-$id@test.com", "password",
                    "User", "Test", UserConstants::TYPE_SEARCH), "Group test", null);
                break;
        }

        return $visitable;
    }
}