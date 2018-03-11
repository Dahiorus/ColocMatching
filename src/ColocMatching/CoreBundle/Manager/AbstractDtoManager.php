<?php

namespace ColocMatching\CoreBundle\Manager;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\Entity\AbstractEntity;
use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Mapper\DtoMapperInterface;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\Searchable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

/**
 * Base implementation of the DTO manager
 *
 * @author Dahiorus
 */
abstract class AbstractDtoManager implements DtoManagerInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var DtoMapperInterface */
    protected $dtoMapper;

    /** @var EntityRepository */
    protected $repository;


    public function __construct(LoggerInterface $logger,
        EntityManagerInterface $em, DtoMapperInterface $dtoMapper)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->repository = $em->getRepository($this->getDomainClass());
        $this->dtoMapper = $dtoMapper;
    }


    /**
     * Gets the string value of the domain class
     * @return string
     */
    protected abstract function getDomainClass() : string;


    /**
     * @inheritdoc
     */
    public function list(PageableFilter $filter) : array
    {
        $this->logger->debug("Getting entities with pagination",
            array ("domainClass" => $this->getDomainClass(), "filter" => $filter));

        return $this->convertEntityListToDto($this->repository->findPage($filter));
    }


    /**
     * @inheritdoc
     */
    public function findAll() : array
    {
        $this->logger->debug("Getting all entities", array ("domainClass" => $this->getDomainClass()));

        return $this->convertEntityListToDto($this->repository->findAll());
    }


    /**
     * @inheritdoc
     */
    public function countAll() : int
    {
        $this->logger->debug("Counting all entities", array ("domainClass" => $this->getDomainClass()));

        return $this->repository->countAll();
    }


    /**
     * @inheritdoc
     */
    public function search(Searchable $filter) : array
    {
        $this->logger->debug("Getting specific entities",
            array ("domainClass" => $this->getDomainClass(), "filter" => $filter));

        return $this->convertEntityListToDto($this->repository->findByFilter($filter));
    }


    /**
     * @inheritdoc
     */
    public function countBy(Searchable $filter) : int
    {
        $this->logger->debug("Counting specific entities",
            array ("domainClass" => $this->getDomainClass(), "filter" => $filter));

        return $this->repository->countByFilter($filter);
    }


    /**
     * @inheritdoc
     */
    public function read(int $id) : AbstractDto
    {
        $this->logger->debug("Getting an entity", array ("domainClass" => $this->getDomainClass(), "id" => $id));

        /** @var EntityInterface $entity */
        $entity = $this->repository->find($id);

        if (empty($entity))
        {
            throw new EntityNotFoundException($this->getDomainClass(), "id", $id);
        }

        return $this->dtoMapper->toDto($entity);
    }


    /**
     * @inheritdoc
     */
    public function delete(AbstractDto $dto, bool $flush = true) : void
    {
        // we have to get the entity corresponding to the DTO
        $entity = $this->repository->find($dto->getId());

        $this->logger->debug("Deleting an entity",
            array ("domainClass" => $this->getDomainClass(), "id" => $dto->getId(), "flush" => $flush));

        $this->em->remove($entity);
        $this->flush($flush);
    }


    /**
     * @inheritdoc
     */
    public function deleteAll() : void
    {
        $this->logger->debug("Deleting all entities", array ("domainClass" => $this->getDomainClass()));

        $entities = $this->findAll();

        foreach ($entities as $entity)
        {
            $this->delete($entity, false);
        }

        $this->flush(true);
    }


    /**
     * Gets the entity referenced by its identifier
     *
     * @param int $id The identifier of the entity
     *
     * @return EntityInterface
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    protected function get(int $id) : EntityInterface
    {
        /** @var EntityInterface $entity */
        $entity = $this->em->getReference($this->getDomainClass(), $id);

        if (empty($entity))
        {
            throw new EntityNotFoundException($this->getDomainClass(), "id", $id);
        }

        return $entity;
    }


    /**
     * Calls the entity manager to flush the operations
     *
     * @param bool $flush If the operations must be flushed
     */
    protected function flush(bool $flush) : void
    {
        if ($flush)
        {
            $this->logger->debug("Flushing operation");

            $this->em->flush();
        }
    }


    /**
     * Converts a list of entities to a list of DTOs
     *
     * @param EntityInterface[] $entities The entities to convert
     *
     * @return AbstractDto[]
     */
    protected function convertEntityListToDto(array $entities) : array
    {
        return array_map(function (AbstractEntity $entity) {
            return $this->dtoMapper->toDto($entity);
        }, $entities);
    }
}