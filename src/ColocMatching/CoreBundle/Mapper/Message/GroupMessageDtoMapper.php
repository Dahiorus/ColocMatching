<?php

namespace ColocMatching\CoreBundle\Mapper\Message;

use ColocMatching\CoreBundle\DTO\Message\GroupMessageDto;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Message\GroupConversation;
use ColocMatching\CoreBundle\Entity\Message\GroupMessage;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class GroupMessageDtoMapper implements DtoMapperInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @param GroupMessage $entity
     *
     * @return null|GroupMessageDto
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new GroupMessageDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setConversationId($entity->getConversation()->getId());
        $dto->setContent($entity->getContent());
        $dto->setAuthorId($entity->getAuthor()->getId());
        $dto->setGroupId($entity->getGroup()->getId());

        if (!empty($entity->getParent()))
        {
            $dto->setParentId($entity->getParent()->getId());
        }

        return $dto;
    }


    /**
     * @param GroupMessageDto $dto
     *
     * @return null|GroupMessage
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $author = $this->entityManager->find(User::class, $dto->getAuthorId());
        $group = $this->entityManager->find(Group::class, $dto->getGroupId());

        $entity = new GroupMessage($author, $group);

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setContent($dto->getContent());

        if (!empty($dto->getConversationId()))
        {
            $conversation = $this->entityManager->find(GroupConversation::class, $dto->getConversationId());
            $entity->setConversation($conversation);
        }

        if (!empty($dto->getParentId()))
        {
            /** @var GroupMessage $parent */
            $parent = $this->entityManager->find($dto->getEntityClass(), $dto->getParentId());
            $entity->setParent($parent);
        }

        return $entity;
    }

}
