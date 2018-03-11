<?php

namespace ColocMatching\CoreBundle\Mapper\Invitation;

use ColocMatching\CoreBundle\DTO\Invitation\InvitationDto;
use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Mapper\DtoMapperInterface;
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

        $dto = InvitationDto::create(get_class($entity->getInvitable()));

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setMessage($entity->getMessage());
        $dto->setStatus($entity->getStatus());
        $dto->setSourceType($entity->getSourceType());
        $dto->setInvitableId($entity->getInvitable()->getId());
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

        /** @var Invitable $invitable */
        $invitable = $this->entityManager->find($dto->getInvitableClass(), $dto->getInvitableId());
        $recipient = $this->entityManager->find(User::class, $dto->getRecipientId());
        $entity = Invitation::create($invitable, $recipient, $dto->getSourceType());

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setMessage($dto->getMessage());
        $entity->setStatus($dto->getStatus());

        return $entity;
    }

}
