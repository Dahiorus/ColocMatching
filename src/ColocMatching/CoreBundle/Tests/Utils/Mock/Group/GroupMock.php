<?php

namespace ColocMatching\CoreBundle\Tests\Utils\Mock\Group;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\CoreBundle\Entity\User\UserConstants;

class GroupMock {


    public static function createGroup(int $id, User $user, string $name, ?string $description): Group {
        $group = new Group($user);

        $group->setId($id);
        $group->setName($name);
        $group->setDescription($description);

        return $group;
    }


    public static function createGroupPage(PageableFilter $filter, int $total): array {
        $groups = array ();

        for ($id = 1; $id <= $total; $id++) {
            $userId = rand(1, 20);
            $groups[] = self::createGroup($id,
                UserMock::createUser($userId, "user-$userId@test.fr", "password", "User $userId firstname",
                    "User $userId lastname", UserConstants::TYPE_SEARCH), "group $id", "Description of group $id");
        }

        return array_slice($groups, $filter->getOffset(), $filter->getSize(), true);
    }


    private function __construct() {
    }

}