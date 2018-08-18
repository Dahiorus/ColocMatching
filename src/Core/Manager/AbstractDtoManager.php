<?php

namespace App\Core\Manager;

use App\Core\DTO\AbstractDto;
use App\Core\Entity\AbstractEntity;
use App\Core\Entity\EntityInterface;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Mapper\DtoMapperInterface;
use App\Core\Repository\EntityRepository;
use App\Core\Repository\Filter\Pageable\Pageable;
use App\Core\Repository\Filter\Searchable;
use Doctrine\ORM\EntityManagerInterface;
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
    public function list(Pageable $pageable = null) : array
    {
        $this->logger->debug("Getting entities",
            array ("domainClass" => $this->getDomainClass(), "pageable" => $pageable));

        $entities = $this->repository->findPage($pageable);

        $this->logger->debug("Entities found",
            array ("count" => count($entities), "domainClass" => $this->getDomainClass()));

        return $this->convertEntityListToDto($entities);
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
    public function search(Searchable $filter, Pageable $pageable = null) : array
    {
        $this->logger->debug("Getting specific entities",
            array ("domainClass" => $this->getDomainClass(), "filter" => $filter, "pageable" => $pageable));

        $entities = $this->repository->findByFilter($filter, $pageable);

        $this->logger->debug("Entities found",
            array ("count" => count($entities), "domainClass" => $this->getDomainClass()));

        return $this->convertEntityListToDto($entities);
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
        $entity = $this->get($id);

        $this->logger->info("Entity found", array ("entity" => $entity));

        return $this->dtoMapper->toDto($entity);
    }


    /**
     * @inheritdoc
     */
    public function delete(AbstractDto $dto, bool $flush = true) : void
    {
        // we have to get the entity corresponding to the DTO
        $entity = $this->get($dto->getId());

        $this->logger->debug("Deleting an entity",
            array ("domainClass" => $this->getDomainClass(), "id" => $dto->getId(), "flush" => $flush));

        $this->em->remove($entity);
        $this->flush($flush);

        $this->logger->debug("Entity deleted", array ("domainClass" => $this->getDomainClass(), "id" => $dto->getId()));
    }


    /**
     * @inheritdoc
     */
    public function deleteAll(bool $flush = true) : void
    {
        $this->logger->debug("Deleting all entities",
            array ("domainClass" => $this->getDomainClass()));

        /** @var AbstractDto[] $dtos */
        $dtos = $this->list();

        $this->logger->debug(sprintf("%d '%s' entities to delete", count($dtos), $this->getDomainClass()));

        array_walk($dtos, function (AbstractDto $dto) {
            $this->delete($dto, false);
        });

        $this->flush($flush);

        $this->logger->info("All entities deleted", array ("domainClass" => $this->getDomainClass()));
    }


    /**
     * Calls the entity manager to flush the operations and clears all managed objects
     *
     * @param bool $flush If the operations must be flushed
     */
    protected function flush(bool $flush) : void
    {
        if ($flush)
        {
            $this->logger->debug("Flushing operation");

            $this->em->flush();
            $this->em->clear();
        }
    }


    /**
     * Gets an entity by its identifier
     *
     * @param int $id The entity identifier
     *
     * @return AbstractEntity
     * @throws EntityNotFoundException
     */
    protected function get(int $id) : AbstractEntity
    {
        /** @var AbstractEntity $entity */
        $entity = $this->repository->find($id);

        if (empty($entity))
        {
            throw new EntityNotFoundException($this->getDomainClass(), "id", $id);
        }

        return $entity;
    }


    /**
     * Converts a list of entities to a list of DTOs
     *
     * @param EntityInterface[] $entities The entities to convert
     * @param DtoMapperInterface $mapper [optional] The DTO mapper to use
     *
     * @return AbstractDto[]
     */
    protected function convertEntityListToDto(array $entities, DtoMapperInterface $mapper = null) : array
    {
        if (empty($mapper))
        {
            $mapper = $this->dtoMapper;
        }

        return array_map(function (AbstractEntity $entity) use ($mapper) {
            return $mapper->toDto($entity);
        }, $entities);
    }

}
