<?php

namespace ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\CoreBundle\Entity\User\UserConstants;

class AnnouncementMock {


    public static function createAnnouncement(int $id, User $user, string $location, string $title, string $type,
        int $rentPrice, \DateTime $startDate): Announcement {
        $announcement = new Announcement($user);

        $announcement->setId($id);
        $announcement->setLocation(AddressMock::createAddress($location));
        $announcement->setTitle($title);
        $announcement->setType($type);
        $announcement->setRentPrice($rentPrice);
        $announcement->setStartDate($startDate);

        return $announcement;
    }


    public static function createAnnouncementArray(PageableFilter $filter, int $total): array {
        $announcements = array ();
        $types = array (Announcement::TYPE_RENT, Announcement::TYPE_SHARING, Announcement::TYPE_SUBLEASE);

        for ($id = 1; $id <= $total; $id++) {
            $userId = rand(1, 20);
            $announcements[] = self::createAnnouncement($id,
                UserMock::createUser($userId, "user-$userId@test.com", "secret", "Usr $userId", "Lastname $userId",
                    UserConstants::TYPE_PROPOSAL), "Paris 75002", "Announcement $id", $types[rand(0, count($types) - 1)],
                rand(50, 850), new \DateTime());
        }

        return array_slice($announcements, $filter->getOffset(), $filter->getSize(), true);
    }


    private function __construct() {
        // empty constructor
    }

}