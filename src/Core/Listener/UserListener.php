<?php

namespace App\Core\Listener;

use App\Core\Entity\Alert\Alert;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\Comment;
use App\Core\Entity\Announcement\HistoricAnnouncement;
use App\Core\Entity\Group\Group;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\Message\GroupMessage;
use App\Core\Entity\Message\PrivateConversation;
use App\Core\Entity\User\DeleteUserEvent;
use App\Core\Entity\User\IdentityProviderAccount;
use App\Core\Entity\User\User;
use App\Core\Entity\Visit\Visit;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Entity listener to update a user password if set
 *
 * @author Dahiorus
 */
class UserListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    public function __construct(LoggerInterface $logger, UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
    }


    /**
     * Set the new encoded password before the persist/merge call
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     * @param User $entity
     */
    public function encodePassword(User $entity)
    {
        if (empty($entity->getPlainPassword()))
        {
            return;
        }

        $this->logger->debug("Setting a new password to the user [{user}]", array ("user" => $entity));

        $newPassword = $this->passwordEncoder->encodePassword($entity, $entity->getPlainPassword());
        $entity->setPassword($newPassword);
    }


    /**
     * Deletes all invitations with the user as recipient
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     */
    public function deleteInvitations(User $entity)
    {
        $repository = $this->entityManager->getRepository(Invitation::class);
        $invitations = $repository->findByRecipient($entity);

        if (!empty($invitations))
        {
            $this->logger->debug("Deleting all invitations with the user [{user}] as recipient",
                array ("user" => $entity));

            $count = $repository->deleteEntities($invitations);

            $this->logger->debug("{count} invitations deleted", array ("count" => $count));
        }
    }


    /**
     * Deletes all visits done by the user
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     */
    public function deleteVisits(User $entity)
    {
        $repository = $this->entityManager->getRepository(Visit::class);
        $visits = $repository->findByVisitor($entity);

        if (!empty($visits))
        {
            $this->logger->debug("Deleting all visits done by [{user}]", array ("user" => $entity));

            $count = $repository->deleteEntities($visits);

            $this->logger->debug("{count} visits deleted", array ("count" => $count));
        }
    }


    /**
     * Deletes all private conversation with the user
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     */
    public function deletePrivateConversations(User $entity)
    {
        $repository = $this->entityManager->getRepository(PrivateConversation::class);
        $conversations = $repository->findByParticipant($entity);

        if (!empty($conversations))
        {
            $this->logger->debug("Deleting all private conversations with [{user}]", array ("user" => $entity));

            foreach ($conversations as $conversation)
            {
                // calling repository deleteEntities() does not cascade to the messages
                $this->entityManager->remove($conversation);
            }

            $this->logger->debug("{count} private conversations deleted", array ("count" => count($conversations)));
        }
    }


    /**
     * Deletes all user historic announcements
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     */
    public function deleteHistoricAnnouncements(User $entity)
    {
        $repository = $this->entityManager->getRepository(HistoricAnnouncement::class);
        $announcements = $repository->findByCreator($entity);

        if (!empty($announcements))
        {
            $this->logger->debug("Deleting all user [{user}] historic announcements", array ("user" => $entity));

            foreach ($announcements as $announcement)
            {
                // calling repository deleteEntities() does not cascade to the comments
                $this->entityManager->remove($announcement);
            }

            $this->logger->debug("{count} historic announcements deleted", array ("count" => count($announcements)));
        }
    }


    /**
     * Deletes all user messages sent in a group
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     */
    public function deleteGroupMessages(User $entity)
    {
        $repository = $this->entityManager->getRepository(GroupMessage::class);
        $messages = $repository->findByAuthor($entity);

        if (!empty($messages))
        {
            $this->logger->debug("Deleting all user [{user}] group messages", array ("user" => $entity));

            $count = $repository->deleteEntities($messages);

            $this->logger->debug("{count} group messages deleted", array ("count" => $count));
        }
    }


    /**
     * Remove the user from the candidate list of an announcement
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     */
    public function removeUserFromAnnouncement(User $entity)
    {
        $repository = $this->entityManager->getRepository(Announcement::class);

        try
        {
            $announcement = $repository->findOneByCandidate($entity);

            if (!empty($announcement))
            {
                $this->logger->debug(
                    "Removing the user [{user}] from the candidate list of the announcement [{announcement}]",
                    array ("user" => $entity, "announcement" => $announcement));

                $announcement->removeCandidate($entity);
                $this->entityManager->merge($announcement);
            }
        }
        catch (NonUniqueResultException $e)
        {
            $this->logger->error("Cannot get the announcement with [{user}] as candidate",
                array ("user" => $entity, "exception" => $e));
        }
    }


    /**
     * Delete the user comments
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     */
    public function deleteComments(User $entity)
    {
        $repository = $this->entityManager->getRepository(Comment::class);
        $comments = $repository->findByAuthor($entity);

        if (!empty($comments))
        {
            $this->logger->debug("Deleting all user [{user}] comments", array ("user" => $entity));

            $count = $repository->deleteEntities($comments);

            $this->logger->debug("{count} comments deleted", array ("count" => $count));
        }
    }


    /**
     * Remove the user from the member list of groups
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     */
    public function removeUserFromGroups(User $entity)
    {
        $repository = $this->entityManager->getRepository(Group::class);
        $groups = $repository->findByMember($entity);

        foreach ($groups as $group)
        {
            $this->logger->debug(
                "Removing the user [{user}] from the member list of the group [{group}]",
                array ("user" => $entity, "group" => $group));

            $group->removeMember($entity);
            $this->entityManager->merge($group);
        }
    }


    /**
     * Delete the user alerts
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     */
    public function deleteAlerts(User $entity)
    {
        $repository = $this->entityManager->getRepository(Alert::class);
        $alerts = $repository->findByUser($entity);

        if (!empty($alerts))
        {
            $this->logger->debug("Deleting the user [{user}] alerts", array ("user" => $entity));

            $count = $repository->deleteEntities($alerts);

            $this->logger->debug("{count} alerts deleted", array ("count" => $count));
        }
    }


    /**
     * Delete the user identity provider accounts
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     */
    public function deleteIdpAccounts(User $entity)
    {
        $repository = $this->entityManager->getRepository(IdentityProviderAccount::class);
        $accounts = $repository->findByUser($entity);

        if (!empty($accounts))
        {
            $this->logger->debug("Deleting the user [{user}] IdP accounts", array ("user" => $entity));

            $count = $repository->deleteEntities($accounts);

            $this->logger->debug("{count} accounts deleted", array ("count" => $count));
        }
    }


    /**
     * Delete the user delete user event
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     * @throws ORMException
     */
    public function deleteDeleteUserEvent(User $entity)
    {
        $repository = $this->entityManager->getRepository(DeleteUserEvent::class);
        $event = $repository->findOneByUser($entity);

        if (!empty($event))
        {
            $this->logger->debug("Deleting the delete user event from the user [{user}]", ["user" => $entity]);

            $this->entityManager->remove($event);
        }
    }

}
