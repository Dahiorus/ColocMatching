<?php

namespace ColocMatching\CoreBundle\Mapper\Message;

use ColocMatching\CoreBundle\DTO\Message\PrivateMessageDto;
use ColocMatching\CoreBundle\Entity\User\PrivateConversation;
use ColocMatching\CoreBundle\Entity\User\PrivateMessage;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class PrivateMessageDtoMapper implements DtoMapperInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @param PrivateMessage $entity
     *
     * @return PrivateMessageDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new PrivateMessageDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setConversationId($entity->getConversation()->getId());
        $dto->setContent($entity->getContent());
        $dto->setAuthorId($entity->getAuthor()->getId());
        $dto->setRecipientId($entity->getRecipient()->getId());

        if (!empty($entity->getParent()))
        {
            $dto->setParentId($entity->getParent()->getId());
        }

        return $dto;
    }


    /**
     * @param PrivateMessageDto $dto
     *
     * @return PrivateMessage|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $author = $this->entityManager->find(User::class, $dto->getAuthorId());
        $recipient = $this->entityManager->find(User::class, $dto->getRecipientId());
        $entity = new PrivateMessage($author, $recipient);

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setContent($dto->getContent());

        if (!empty($dto->getConversationId()))
        {
            $conversation = $this->entityManager->find(PrivateConversation::class, $dto->getConversationId());
            $entity->setConversation($conversation);
        }

        if (!empty($dto->getParentId()))
        {
            /** @var PrivateMessage $parent */
            $parent = $this->entityManager->find($dto->getEntityClass(), $dto->getParentId());
            $entity->setParent($parent);
        }

        return $entity;
    }

}