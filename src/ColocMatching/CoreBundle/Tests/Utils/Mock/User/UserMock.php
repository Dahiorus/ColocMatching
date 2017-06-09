<?php

namespace ColocMatching\CoreBundle\Tests\Utils\Mock\User;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Entity\User\UserConstants;

class UserMock {


    public static function createUser(int $id, string $email, string $plainPassword, string $firstname, string $lastname,
        string $type): User {
        $user = new User();

        $user->setId($id);
        $user->setEmail($email);
        $user->setPlainPassword($plainPassword);
        $user->setPassword(password_hash($plainPassword, PASSWORD_BCRYPT, array ("cost" => 12)));
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setType($type);

        return $user;
    }


    public static function createUserPage(PageableFilter $filter, int $total): array {
        $users = array ();

        for ($id = 1; $id <= $total; $id++) {
            $users[] = self::createUser($id, "user." . $id . "@test.com", "password", "User " . $id, "Lastname",
                ($id % 7) == 0 ? UserConstants::TYPE_PROPOSAL : UserConstants::TYPE_SEARCH);
        }

        return array_slice($users, $filter->getOffset(), $filter->getSize(), true);
    }


    public static function createUserArray(int $total) {
        $users = array ();

        for ($id = 1; $id <= $total; $id++) {
            $users[] = self::createUser($id, "user." . $id . "@test.com", "password", "User " . $id, "Lastname",
                ($id % 7) == 0 ? UserConstants::TYPE_PROPOSAL : UserConstants::TYPE_SEARCH);
        }

        return $users;
    }


    private function __construct() {
        // empty constructor
    }

}