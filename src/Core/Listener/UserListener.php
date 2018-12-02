<?php

namespace App\Core\Listener;

use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\HistoricAnnouncement;
use App\Core\Entity\Group\Group;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\Message\GroupMessage;
use App\Core\Entity\Message\PrivateConversation;
use App\Core\Entity\User\User;
use App\Core\Entity\Visit\Visit;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
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
        $privateConversations = $repository->findByParticipant($entity);

        if (!empty($privateConversations))
        {
            $this->logger->debug("Deleting all private conversations with [{user}]", array ("user" => $entity));

            $count = $repository->deleteEntities($privateConversations);

            $this->logger->debug("{count} private conversations deleted", array ("count" => $count));
        }
    }


    /**
     * Deletes all user historic announcements
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     */
    public function deleteHistoricAnnouncement(User $entity)
    {
        $repository = $this->entityManager->getRepository(HistoricAnnouncement::class);
        $announcements = $repository->findByCreator($entity);

        if (!empty($announcements))
        {
            $this->logger->debug("Deleting all user [{user}] historic announcements", array ("user" => $entity));

            $count = $repository->deleteEntities($announcements);

            $this->logger->debug("{count} historic announcements deleted", array ("count" => $count));
        }
    }


    /**
     * Deletes all user messages sent in a group
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     */
    public function deleteGroupMessage(User $entity)
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
     * Delete the user announcement
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     */
    public function deleteAnnouncement(User $entity)
    {
        if ($entity->hasAnnouncement())
        {
            $this->logger->debug("Deleting the user [{user}] announcement", array ("user" => $entity));

            $announcement = $entity->getAnnouncement();
            $this->entityManager->remove($announcement);
            $entity->setAnnouncement(null);
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
     * Remove the user from the member list of a group
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     */
    public function removeUserFromGroup(User $entity)
    {
        $repository = $this->entityManager->getRepository(Group::class);

        try
        {
            $group = $repository->findOneByMember($entity);

            if (!empty($group))
            {
                $this->logger->debug(
                    "Removing the user [{user}] from the member list of the group [{group}]",
                    array ("user" => $entity, "group" => $group));

                $group->removeMember($entity);
                $this->entityManager->merge($group);
            }
        }
        catch (NonUniqueResultException $e)
        {
            $this->logger->error("Cannot get the group with [{user}] as member",
                array ("user" => $entity, "exception" => $e));
        }
    }


    /**
     * Delete the user announcement
     *
     * @ORM\PreRemove
     *
     * @param User $entity
     */
    public function deleteGroup(User $entity)
    {
        if ($entity->hasGroup())
        {
            $this->logger->debug("Deleting the user [{user}] group", array ("user" => $entity));

            $group = $entity->getGroup();
            $this->entityManager->remove($group);
            $entity->setGroup(null);
        }
    }

}
