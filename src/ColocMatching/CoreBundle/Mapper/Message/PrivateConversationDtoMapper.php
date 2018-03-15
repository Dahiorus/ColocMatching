<?php

namespace ColocMatching\CoreBundle\Mapper\Message;

use ColocMatching\CoreBundle\DTO\Message\PrivateConversationDto;
use ColocMatching\CoreBundle\DTO\Message\PrivateMessageDto;
use ColocMatching\CoreBundle\Entity\User\PrivateConversation;
use ColocMatching\CoreBundle\Entity\User\PrivateMessage;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Mapper\DtoMapperInterface;
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