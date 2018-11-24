<?php

namespace App\Core\Mapper\Alert;

use App\Core\DTO\Alert\AlertDto;
use App\Core\Entity\Alert\Alert;
use App\Core\Entity\User\User;
use App\Core\Exception\UnsupportedSerializationException;
use App\Core\Mapper\DtoMapperInterface;
use App\Core\Repository\Filter\Converter\StringConverterInterface;
use App\Core\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class AlertDtoMapper implements DtoMapperInterface
{
    /** @var UserRepository */
    private $userRepository;

    /** @var StringConverterInterface */
    private $filterStringConverter;


    public function __construct(EntityManagerInterface $entityManager, StringConverterInterface $filterStringConverter)
    {
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->filterStringConverter = $filterStringConverter;
    }


    /**
     * @param Alert $entity
     *
     * @return AlertDto|null
     */
    public function toDto($entity)
    {
        if (empty($entity))
        {
            return null;
        }

        $dto = new AlertDto();

        $dto->setId($entity->getId());
        $dto->setCreatedAt($entity->getCreatedAt());
        $dto->setLastUpdate($entity->getLastUpdate());
        $dto->setUserId($entity->getUser()->getId());
        $dto->setName($entity->getName());
        $dto->setNotificationType($entity->getNotificationType());
        $dto->setSearchPeriod($entity->getSearchPeriod());
        $dto->setStatus($entity->getStatus());
        $dto->setResultSize($entity->getResultSize());

        try
        {
            $dto->setFilter($this->filterStringConverter->toObject($entity->getFilter(), $entity->getFilterClass()));
        }
        catch (UnsupportedSerializationException $e)
        {
            $dto->setFilter(null);
        }

        return $dto;
    }


    /**
     * @param AlertDto $dto
     *
     * @return Alert|null
     */
    public function toEntity($dto)
    {
        if (empty($dto))
        {
            return null;
        }

        /** @var User $user */
        $user = $this->userRepository->find($dto->getUserId());
        /** @var string $filter */
        $filter = $this->filterStringConverter->toString($dto->getFilter());

        $entity = new Alert($user, get_class($dto->getFilter()), $filter);

        $entity->setId($dto->getId());
        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setLastUpdate($dto->getLastUpdate());
        $entity->setName($dto->getName());
        $entity->setNotificationType($dto->getNotificationType());
        $entity->setSearchPeriod($dto->getSearchPeriod());
        $entity->setStatus($dto->getStatus());
        $entity->setResultSize($dto->getResultSize());

        return $entity;
    }

}