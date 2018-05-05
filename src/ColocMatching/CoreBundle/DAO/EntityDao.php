<?php

namespace ColocMatching\CoreBundle\DAO;

use ColocMatching\CoreBundle\Entity\AbstractEntity;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\Pageable;
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
    public function list(Pageable $pageable = null) : array
    {
        return $this->repository->findPage($pageable);
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
    public function search(Searchable $filter, Pageable $pageable = null) : array
    {
        return $this->repository->findByFilter($filter, $pageable);
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
    public function findBy(array $criteria = array (), Pageable $pageable = null) : array
    {
        $orderBy = array ();

        foreach ($pageable->getSorts() as $sort)
        {
            $orderBy[ $sort->getProperty() ] = $sort->getDirection();
        }

        return $this->repository->findBy($criteria, $orderBy, $pageable->getSize(), $pageable->getOffset());
    }


    /**
     * @inheritdoc
     */
    public function count(array $criteria = array ()) : int
    {
        return $this->repository->count($criteria);
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
    public function persist(AbstractEntity $entity) : AbstractEntity
    {
        $this->entityManager->persist($entity);

        return $entity;
    }


    /**
     * @inheritdoc
     */
    public function merge(AbstractEntity $entity) : AbstractEntity
    {
        /** @var AbstractEntity $mergedEntity */
        $mergedEntity = $this->entityManager->merge($entity);

        return $mergedEntity;
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
            $this->throwEntityNotFound("id", $id);
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
            $this->throwEntityNotFound("id", $id);
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
        $this->repository->deleteAll();
        $this->entityManager->clear();
    }


    /**
     * @inheritdoc
     */
    public function flush() : void
    {
        $this->entityManager->flush();
    }


    /**
     * Throws an EntityNotFoundException
     *
     * @param string $propertyName The property name used to get the entity
     * @param mixed $value The property value
     *
     * @throws EntityNotFoundException
     */
    private function throwEntityNotFound(string $propertyName, $value)
    {
        throw new EntityNotFoundException($this->getDomainClass(), $propertyName, $value);
    }


    abstract protected function getDomainClass() : string;

}
