<?php

namespace ColocMatching\CoreBundle\Mapper\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\CommentDto;
use ColocMatching\CoreBundle\Entity\Announcement\Comment;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Mapper\DtoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class CommentDtoMapper implements DtoMapperInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @param Comment $entity
     *
     * @return CommentDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new CommentDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setAuthorId($entity->getAuthor()->getId());
        $dto->setMessage($entity->getMessage());
        $dto->setRate($entity->getRate());

        return $dto;
    }


    /**
     * @param CommentDto $dto
     *
     * @return Comment|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        $author = $this->entityManager->find(User::class, $dto->getAuthorId());
        $entity = new Comment($author);

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setMessage($dto->getMessage());
        $entity->setRate($dto->getRate());

        return $entity;
    }

}