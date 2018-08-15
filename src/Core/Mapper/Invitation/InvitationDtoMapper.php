<?php

namespace App\Core\Mapper\Invitation;

use App\Core\DTO\Invitation\InvitationDto;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\User\User;
use App\Core\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class InvitationDtoMapper implements DtoMapperInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @param Invitation $entity
     *
     * @return InvitationDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new InvitationDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setMessage($entity->getMessage());
        $dto->setStatus($entity->getStatus());
        $dto->setSourceType($entity->getSourceType());
        $dto->setInvitableClass($entity->getInvitableClass());
        $dto->setInvitableId($entity->getInvitableId());
        $dto->setRecipientId($entity->getRecipient()->getId());

        return $dto;
    }


    /**
     * @param InvitationDto $dto
     *
     * @return Invitation|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $recipient = $this->entityManager->find(User::class, $dto->getRecipientId());
        $entity = new Invitation($dto->getInvitableClass(), $dto->getInvitableId(), $recipient, $dto->getSourceType());

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setMessage($dto->getMessage());
        $entity->setStatus($dto->getStatus());

        return $entity;
    }

}
