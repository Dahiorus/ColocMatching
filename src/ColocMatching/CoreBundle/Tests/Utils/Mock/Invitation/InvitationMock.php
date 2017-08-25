<?php

namespace ColocMatching\CoreBundle\Tests\Utils\Mock\Invitation;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Group\GroupMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;

class InvitationMock {

    public static function createInvitation(int $id, Invitable $invitable, User $recipient,
        string $sourceType) : Invitation {
        $invitation = Invitation::create($invitable, $recipient, $sourceType);

        $invitation->setId($id);

        return $invitation;
    }


    public static function createInvitationPage(PageableFilter $filter, int $total, string $invitableClass,
        User $recipient = null) {
        $invitations = array ();

        for ($id = 1; $id <= $total; $id++) {
            $invitable = self::buildInvitable($invitableClass);

            if (empty($recipient)) {
                $recipient = UserMock::createUser(random_int(1, 50), "search@test.fr", "password", "Search", "Test",
                    UserConstants::TYPE_SEARCH);
            }

            $sourceType = ($id % 6 == 4) ? Invitation::SOURCE_INVITABLE : Invitation::SOURCE_SEARCH;

            $invitations[] = self::createInvitation($id, $invitable, $recipient, $sourceType);
        }

        return array_slice($invitations, $filter->getOffset(), $filter->getSize(), true);
    }


    public static function createInvitationPageForInvitable(PageableFilter $filter, int $total, Invitable $invitable,
        User $recipient = null) {
        $invitations = array ();

        for ($id = 1; $id <= $total; $id++) {
            if (empty($recipient)) {
                $recipient = UserMock::createUser(random_int(1, 50), "search@test.fr", "password", "Search", "Test",
                    UserConstants::TYPE_SEARCH);
            }

            $sourceType = ($id % 6 == 4) ? Invitation::SOURCE_INVITABLE : Invitation::SOURCE_SEARCH;

            $invitations[] = self::createInvitation($id, $invitable, $recipient, $sourceType);
        }

        return array_slice($invitations, $filter->getOffset(), $filter->getSize(), true);
    }


    private static function buildInvitable(string $invitableClass) {
        $invitableId = random_int(1, 100);
        $invitable = null;

        switch ($invitableClass) {
            case Announcement::class:
                $creator = UserMock::createUser(random_int(1, 100), "user@test.fr",
                    "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
                $invitable = AnnouncementMock::createAnnouncement($invitableId, $creator,
                    "Paris 75015", "Test announcement", Announcement::TYPE_RENT,
                    980,
                    new \DateTime());
                break;
            case Group::class:
                $creator = UserMock::createUser(random_int(1, 100), "user@test.fr",
                    "password", "User", "Test", UserConstants::TYPE_SEARCH);
                $invitable = GroupMock::createGroup($invitableId, $creator, "Group test", "Group from test");
                break;
        }

        return $invitable;
    }
}