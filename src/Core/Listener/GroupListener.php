<?php

namespace App\Core\Listener;

use App\Core\Entity\Group\Group;
use App\Core\Entity\Message\GroupConversation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

class GroupListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }


    /**
     * Deletes the group conversation
     *
     * @ORM\PreRemove
     *
     * @param Group $entity
     *
     * @throws ORMException
     */
    public function deleteConversation(Group $entity)
    {
        $repository = $this->entityManager->getRepository(GroupConversation::class);
        $conversation = $repository->findOneByGroup($entity);

        if (!empty($conversation))
        {
            $this->logger->debug("Deleting the group [{group}] conversation",
                array ("group" => $entity, "conversation" => $conversation));

            $this->entityManager->remove($conversation);

            $this->logger->debug("Conversation [{conversation}] deleted", array ("conversation" => $conversation));
        }
    }

}
