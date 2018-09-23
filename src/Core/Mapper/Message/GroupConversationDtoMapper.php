<?php

namespace App\Core\Mapper\Message;

use App\Core\DTO\Message\GroupConversationDto;
use App\Core\DTO\Message\GroupMessageDto;
use App\Core\Entity\Group\Group;
use App\Core\Entity\Message\GroupConversation;
use App\Core\Entity\Message\GroupMessage;
use App\Core\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class GroupConversationDtoMapper implements DtoMapperInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var GroupMessageDtoMapper */
    private $messageDtoMapper;


    public function __construct(EntityManagerInterface $entityManager, GroupMessageDtoMapper $messageDtoMapper)
    {
        $this->entityManager = $entityManager;
        $this->messageDtoMapper = $messageDtoMapper;
    }


    /**
     * @param GroupConversation $entity
     *
     * @return GroupConversationDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new GroupConversationDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setGroupId($entity->getGroup()->getId());
        $dto->setMessages($entity->getMessages()->map(function (GroupMessage $msg) {
            return $this->messageDtoMapper->toDto($msg);
        }));

        return $dto;
    }


    /**
     * @param GroupConversationDto $dto
     *
     * @return GroupConversation|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $group = $this->entityManager->find(Group::class, $dto->getGroupId());

        $entity = new GroupConversation($group);

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setMessages($dto->getMessages()->map(function (GroupMessageDto $msg) {
            return empty($msg->getId()) ?
                $this->messageDtoMapper->toEntity($msg)
                : $this->entityManager->find($msg->getEntityClass(), $msg->getId());
        }));

        return $entity;
    }

}