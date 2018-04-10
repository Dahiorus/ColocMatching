<?php

namespace ColocMatching\CoreBundle\DAO;

use ColocMatching\CoreBundle\Entity\AbstractEntity;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\Searchable;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Generic class representing an entity DAO
 * @author Dahiorus
 */
abstract class EntityDao implements DAO
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var EntityRepository */
    protected $repository;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository($this->getDomainClass());
    }


    /**
     * @inheritdoc
     */
    public function list(PageableFilter $filter) : array
    {
        return $this->repository->findPage($filter);
    }


    /**
     * @inheritdoc
     */
    public function findAll() : array
    {
        return $this->repository->findAll();
    }


    /**
     * @inheritdoc
     */
    public function countAll() : int
    {
        return $this->repository->countAll();
    }


    /**
     * @inheritdoc
     */
    public function search(Searchable $filter) : array
    {
        return $this->repository->findByFilter($filter);
    }


    /**
     * @inheritdoc
     */
    public function countBy(Searchable $filter) : int
    {
        return $this->repository->countByFilter($filter);
    }


    /**
     * @inheritdoc
     */
    public function findOne(array $criteria = array ())
    {
        return $this->repository->findOneBy($criteria);
    }


    /**
     * @inheritdoc
     */
    public function save(AbstractEntity $entity) : AbstractEntity
    {
        if (empty($entity->getId()))
        {
            $this->entityManager->persist($entity);
        }
        else
        {
            $entity = $this->entityManager->merge($entity);
        }

        return $entity;
    }


    /**
     * @inheritdoc
     */
    public function get(int $id) : AbstractEntity
    {
        /** @var AbstractEntity $entity */
        $entity = $this->entityManager->getReference($this->getDomainClass(), $id);

        if (empty($entity))
        {
            throw new EntityNotFoundException($this->getDomainClass(), "id", $id);
        }

        return $entity;
    }


    /**
     * @inheritdoc
     */
    public function read(int $id) : AbstractEntity
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
     * @inheritdoc
     */
    public function delete(AbstractEntity $entity) : void
    {
        $this->entityManager->remove($entity);
    }


    /**
     * @inheritdoc
     */
    public function deleteAll() : void
    {
        $entities = $this->findAll();

        array_walk($entities, function (AbstractEntity $entity) {
            $this->delete($entity);
        });
    }


    /**
     * @inheritdoc
     */
    public function flush() : void
    {
        $this->entityManager->flush();
    }


    /**
     * Gets the entity class of the DAO
     * @return string
     */
    abstract protected function getDomainClass() : string;

}