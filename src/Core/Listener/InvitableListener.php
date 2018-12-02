<?php

namespace App\Core\Listener;

use App\Core\Entity\Invitation\Invitable;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Repository\Invitation\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;

class InvitableListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var InvitationRepository
     */
    private $invitationRepository;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->invitationRepository = $entityManager->getRepository(Invitation::class);
    }


    /**
     * Sets the creation date to the entity before persisting
     *
     * @ORM\PreRemove
     *
     * @param Invitable $entity
     *
     * @throws \Exception
     */
    public function deleteInvitations(Invitable $entity)
    {
        $entityName = $this->entityManager->getMetadataFactory()->getMetadataFor(get_class($entity))->getName();
        $invitations = $this->invitationRepository->findBy(array (
            "invitableClass" => $entityName,
            "invitableId" => $entity->getId()
        ));

        if (!empty($invitations))
        {
            $this->logger->debug("Deleting all [{entity}] invitations",
                array ("entity" => $entity, "invitations" => $invitations));

            $count = $this->invitationRepository->deleteEntities($invitations);

            $this->logger->debug("{count} invitations deleted", array ("count" => $count));
        }
    }

}