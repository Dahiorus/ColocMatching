<?php

namespace ColocMatching\CoreBundle\Tests\Utils\Mock\Visit;


use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
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


    public static function createVisitPage(PageableFilter $filter, int $total, string $visitedClass, User $visitor = null) : array {
        $visits = array ();

        for ($id = 1; $id <= $total; $id++) {
            $visited = self::buildVisitable($visitedClass);

            if (empty($visitor)) {
                $userId = random_int(1, 10);
                $visitor = UserMock::createUser($userId, "user-test-$userId@test.com", "password", "User", "Test",
                    ($userId % 2 == 0) ? UserConstants::TYPE_PROPOSAL : UserConstants::TYPE_SEARCH);
            }

            $visits[] = self::createVisit($id, $visited, $visitor, new \DateTime());
        }

        return array_slice($visits, $filter->getOffset(), $filter->getSize(), true);
    }


    public static function createVisitPageForVisited(PageableFilter $filter, int $total, Visitable $visited, User $visitor = null) {
        $visits = array ();

        for ($id = 1; $id <= $total; $id++) {
            if (empty($visitor)) {
                $userId = random_int(1, 10);
                $visitor = UserMock::createUser($userId, "user-test-$userId@test.com", "password", "User", "Test",
                    ($userId % 2 == 0) ? UserConstants::TYPE_PROPOSAL : UserConstants::TYPE_SEARCH);
            }

            $visits[] = self::createVisit($id, $visited, $visitor, new \DateTime());
        }

        return array_slice($visits, $filter->getOffset(), $filter->getSize(), true);
    }


    private static function buildVisitable(string $visitedClass) : Visitable {
       $visitedId = random_int(1, 50);
        $visitable = null;

        switch ($visitedClass) {
            case Announcement::class:
                $visitable = AnnouncementMock::createAnnouncement(
                    $visitedId,
                    UserMock::createUser($visitedId, "proposal-$visitedId@test.com", "password", "User", "Test",
                        UserConstants::TYPE_PROPOSAL), "Paris 75005", "Announcement test", Announcement::TYPE_RENT,
                    1400, new \DateTime());
                break;
            case User::class:
                $visitable = UserMock::createUser($visitedId, "user-$visitedId@test.com", "password", "User", "Test",
                    UserConstants::TYPE_PROPOSAL);
                break;
            case Group::class:
                $visitable = GroupMock::createGroup(
                    $visitedId,
                    UserMock::createUser($visitedId, "search-$visitedId@test.com", "password",
                        "User", "Test", UserConstants::TYPE_SEARCH), "Group test", null);
                break;
        }

        return $visitable;
    }

}