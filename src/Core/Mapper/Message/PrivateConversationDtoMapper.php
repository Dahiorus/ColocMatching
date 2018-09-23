<?php

namespace App\Core\Mapper\Message;

use App\Core\DTO\Message\PrivateConversationDto;
use App\Core\DTO\Message\PrivateMessageDto;
use App\Core\Entity\Message\PrivateConversation;
use App\Core\Entity\Message\PrivateMessage;
use App\Core\Entity\User\User;
use App\Core\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class PrivateConversationDtoMapper implements DtoMapperInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var PrivateMessageDtoMapper */
    private $messageDtoMapper;


    public function __construct(EntityManagerInterface $entityManager, PrivateMessageDtoMapper $messageDtoMapper)
    {
        $this->entityManager = $entityManager;
        $this->messageDtoMapper = $messageDtoMapper;
    }


    /**
     * @param PrivateConversation $entity
     *
     * @return PrivateConversationDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new PrivateConversationDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setFirstParticipantId($entity->getFirstParticipant()->getId());
        $dto->setSecondParticipantId($entity->getSecondParticipant()->getId());
        $dto->setMessages($entity->getMessages()->map(function (PrivateMessage $msg) {
            return $this->messageDtoMapper->toDto($msg);
        }));

        return $dto;
    }


    /**
     * @param PrivateConversationDto $dto
     *
     * @return PrivateConversation|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $firstParticipant = $this->entityManager->find(User::class, $dto->getFirstParticipantId());
        $secondParticipant = $this->entityManager->find(User::class, $dto->getSecondParticipantId());
        $entity = new PrivateConversation($firstParticipant, $secondParticipant);

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setMessages($dto->getMessages()->map(function (PrivateMessageDto $msg) {
            return empty($msg->getId()) ? $this->messageDtoMapper->toEntity($msg)
                : $this->entityManager->find($msg->getEntityClass(), $msg->getId());
        }));

        return $entity;
    }
}