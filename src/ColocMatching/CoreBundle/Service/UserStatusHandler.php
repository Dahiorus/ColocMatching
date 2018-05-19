<?php

namespace ColocMatching\CoreBundle\Service;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Event\DeleteAnnouncementEvent;
use ColocMatching\CoreBundle\Repository\Announcement\AnnouncementRepository;
use ColocMatching\CoreBundle\Repository\Group\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Handler for operation on a user status.
 *
 * @author Dahiorus
 */
class UserStatusHandler
{
    /** @var LoggerInterface */
    private $logger;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var AnnouncementRepository */
    private $announcementRepository;

    /** @var GroupRepository */
    private $groupRepository;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
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
        $this->logger->debug("Banning a user", array ("user" => $user));

        $this->handleAnnouncementBanLink($user);
        $this->handleGroupBanLink($user);

        $user->setStatus(UserConstants::STATUS_BANNED);

        /** @var User $bannedUser */
        $bannedUser = $this->entityManager->merge($user);

        if ($flush)
        {
            $this->entityManager->flush();
        }

        $this->logger->debug("User banned", array ("user" => $bannedUser));

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
        $this->logger->debug("Disabling a user", array ("user" => $user));

        // disable the user announcement
        if ($user->hasAnnouncement())
        {
            $announcement = $user->getAnnouncement();

            $this->logger->debug("Disabling the announcement of the user", array ("announcement" => $announcement));

            $announcement->setStatus(Announcement::STATUS_DISABLED);
            $announcement = $this->entityManager->merge($announcement);

            $this->logger->debug("Announcement disabled", array ("announcement" => $announcement));
        }

        // close the user group
        if ($user->hasGroup())
        {
            $group = $user->getGroup();

            $this->logger->debug("Closing the group of the user", array ("group" => $group));

            $group->setStatus(Group::STATUS_CLOSED);
            $group = $this->entityManager->merge($group);

            $this->logger->debug("Group closed", array ("group" => $group));
        }

        $user->setStatus(UserConstants::STATUS_VACATION);

        /** @var User $disabledUser */
        $disabledUser = $this->entityManager->merge($user);

        if ($flush)
        {
            $this->entityManager->flush();
        }

        $this->logger->debug("User disabled", array ("user" => $disabledUser));

        return $disabledUser;
    }


    /**
     * <p>Enables a user.</p>
     * <p>If the user has an announcement, the announcement is enabled.</p>
     * <p>If the user has a group, the group is opened.</p>
     *
     * @param User $user The user to enable
     * @param bool $flush If all entity operations must be flushed
     *
     * @return User
     */
    public function enable(User $user, bool $flush) : User
    {
        $this->logger->debug("Enabling a user", array ("user" => $user));

        // enable the user announcement
        if ($user->hasAnnouncement())
        {
            $announcement = $user->getAnnouncement();

            $this->logger->debug("Enabling the announcement of the user", array ("announcement" => $announcement));

            $announcement->setStatus(Announcement::STATUS_ENABLED);
            $this->entityManager->merge($announcement);

            $this->logger->debug("Announcement enabled", array ("announcement" => $announcement));
        }

        // open the user group
        if ($user->hasGroup())
        {
            $group = $user->getGroup();

            if ($group->getStatus() == Group::STATUS_CLOSED)
            {
                $this->logger->debug("Opening the group of the user", array ("group" => $group));

                $group->setStatus(Group::STATUS_OPENED);
                $this->entityManager->merge($group);

                $this->logger->debug("Group opened", array ("group" => $group));
            }
        }

        $user->setStatus(UserConstants::STATUS_ENABLED);

        /** @var User $enabledUser */
        $enabledUser = $this->entityManager->merge($user);

        if ($flush)
        {
            $this->entityManager->flush();
        }

        $this->logger->debug("User enabled", array ("user" => $enabledUser));

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
        if ($user->hasAnnouncement())
        {
            $announcement = $user->getAnnouncement();

            $this->logger->debug("Deleting the user announcement", array ("announcement" => $announcement));

            $this->eventDispatcher->dispatch(
                DeleteAnnouncementEvent::DELETE_EVENT, new DeleteAnnouncementEvent($announcement->getId()));
            $this->entityManager->remove($announcement);
            $user->setAnnouncement(null);

            $this->logger->debug("Announcement deleted");

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
     *
     * @throws ORMException
     */
    private function handleGroupBanLink(User $user) : void
    {
        // the user has a group -> remove or replace creator
        if ($user->hasGroup())
        {
            $group = $user->getGroup();
            $group->removeMember($user);
            $user->setGroup(null);

            if ($group->hasMembers())
            {
                /** @var User $newCreator */
                $newCreator = $group->getMembers()->first();

                $this->logger->debug("Changing the banned user group creator",
                    array ("group" => $group, "newCreator" => $newCreator));

                $group->setCreator($newCreator);
                $newCreator->setGroup($group);

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

        $group = $this->groupRepository->findOneByMember($user);

        if (empty($group))
        {
            return;
        }

        // the user is in a group -> remove the user from the group
        $this->logger->debug("Removing the user from a group", array ("group" => $group));

        $group->removeMember($user);
        $this->entityManager->merge($group);
    }

}
