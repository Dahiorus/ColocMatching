<?php

namespace App\Core\Service;

use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Group\Group;
use App\Core\Entity\User\User;
use App\Core\Entity\User\UserStatus;
use App\Core\Repository\Announcement\AnnouncementRepository;
use App\Core\Repository\Group\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

/**
 * Handler for operation on a user status.
 *
 * @author Dahiorus
 */
class UserStatusHandler
{
    /** @var LoggerInterface */
    private $logger;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var AnnouncementRepository */
    private $announcementRepository;

    /** @var GroupRepository */
    private $groupRepository;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->announcementRepository = $entityManager->getRepository(Announcement::class);
        $this->groupRepository = $entityManager->getRepository(Group::class);
    }


    /**
     * <p>Bans a user.</p>
     * <p>If the user has an announcement, the announcement is deleted and all candidates are informed.</p>
     * <p>If the user has a group, the user is no longer the creator and an other member becomes de creator, unless the
     * group an no other member, in this case the group is deleted.</p>
     * <p>If the user is an announcement candidate, the user is removed from the announcement.</p>
     * <p>If the user is in a group, the user is removed from the group.</p>
     *
     * @param User $user The user to ban
     * @param bool $flush If all entity operations must be flushed
     *
     * @return User
     * @throws ORMException
     */
    public function ban(User $user, bool $flush) : User
    {
        $this->logger->debug("Banning the user [{user}]", array ("user" => $user));

        $this->handleAnnouncementBanLink($user);
        $this->handleGroupBanLink($user);

        $user->setStatus(UserStatus::BANNED);

        /** @var User $bannedUser */
        $bannedUser = $this->entityManager->merge($user);

        if ($flush)
        {
            $this->entityManager->flush();
        }

        $this->logger->debug("User banned [{user}]", array ("user" => $bannedUser));

        return $bannedUser;
    }


    /**
     * <p>Disables a user.</p>
     * <p>If the user has an announcement, the announcement is disabled.</p>
     * <p>If the user has a group, the group is closed.</p>
     *
     * @param User $user The user to disable
     * @param bool $flush If all entity operations must be flushed
     *
     * @return User
     */
    public function disable(User $user, bool $flush) : User
    {
        $this->logger->debug("Disabling the user [{user}]", array ("user" => $user));

        // disable the user announcements
        foreach ($user->getAnnouncements() as $announcement)
        {
            $this->logger->debug("Disabling the announcement of the user", array ("announcement" => $announcement));

            $announcement->setStatus(Announcement::STATUS_DISABLED);
            $announcement = $this->entityManager->merge($announcement);

            $this->logger->debug("Announcement disabled [{announcement}]", array ("announcement" => $announcement));
        }

        // close the user groups
        foreach ($user->getGroups() as $group)
        {
            $this->logger->debug("Closing the group of the user", array ("group" => $group));

            $group->setStatus(Group::STATUS_CLOSED);
            $group = $this->entityManager->merge($group);

            $this->logger->debug("Group closed [{group}]", array ("group" => $group));
        }

        $user->setStatus(UserStatus::VACATION);

        /** @var User $disabledUser */
        $disabledUser = $this->entityManager->merge($user);

        if ($flush)
        {
            $this->entityManager->flush();
        }

        $this->logger->debug("User disabled [{user}]", array ("user" => $disabledUser));

        return $disabledUser;
    }


    /**
     * Enables a user.
     * If the user has an announcement, the announcement is enabled.
     * If the user has a group, the group is opened.
     *
     * @param User $user The user to enable
     * @param bool $flush If all entity operations must be flushed
     *
     * @return User
     */
    public function enable(User $user, bool $flush) : User
    {
        $this->logger->debug("Enabling the user [{user}]", array ("user" => $user));

        $user->setStatus(UserStatus::ENABLED);

        /** @var User $enabledUser */
        $enabledUser = $this->entityManager->merge($user);

        if ($flush)
        {
            $this->entityManager->flush();
        }

        $this->logger->debug("User enabled [{user}]", array ("user" => $enabledUser));

        return $enabledUser;
    }


    /**
     * Handles the user announcement links on a ban
     *
     * @param User $user The user to ban
     *
     * @throws ORMException
     */
    private function handleAnnouncementBanLink(User $user) : void
    {
        // the user has an announcement -> delete the announcement and inform the candidates
        if ($user->hasAnnouncements())
        {
            $announcements = $user->getAnnouncements();

            $this->logger->debug("Deleting the user [{user}] announcements",
                array ("user" => $user, "announcements" => $announcements));

            foreach ($announcements as $announcement)
            {
                $this->entityManager->remove($announcement);
                $user->removeAnnouncement($announcement);

                $this->logger->debug("Announcement deleted [{announcement}]", array ("announcement" => $announcement));
            }

            return;
        }

        $announcement = $this->announcementRepository->findOneByCandidate($user);

        if (empty($announcement))
        {
            return;
        }

        // the user is an announcement candidate -> remove the user from the announcement
        $this->logger->debug("Removing the user from an announcement", array ("announcement" => $announcement));

        $announcement->removeCandidate($user);
        $this->entityManager->merge($announcement);
    }


    /**
     * Handles the user group links on a ban
     *
     * @param User $user The user to ban
     */
    private function handleGroupBanLink(User $user) : void
    {
        // the user has a group -> remove or replace creator
        if ($user->hasGroups())
        {
            $groups = $user->getGroups();

            foreach ($groups as $group)
            {
                $group->removeMember($user);
                $user->removeGroup($group);

                if ($group->hasMembers())
                {
                    /** @var User $newCreator */
                    $newCreator = $group->getMembers()->first();

                    $this->logger->debug("Changing the banned user group creator",
                        array ("group" => $group, "newCreator" => $newCreator));

                    $group->setCreator($newCreator);
                    $newCreator->addGroup($group);

                    $this->entityManager->merge($newCreator);
                    $this->entityManager->merge($group);
                }
                else
                {
                    $this->logger->debug("Deleting the banned user group", array ("group" => $group));

                    $this->entityManager->remove($group);

                    $this->logger->debug("Group deleted");
                }

                $this->entityManager->merge($user);

                return;
            }
        }

        $groups = $this->groupRepository->findByMember($user);

        foreach ($groups as $group)
        {
            // the user is in a group -> remove the user from the group
            $this->logger->debug("Removing the user from a group", array ("group" => $group));

            $group->removeMember($user);
            $this->entityManager->merge($group);
        }
    }

}
