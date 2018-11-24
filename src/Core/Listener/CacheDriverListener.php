<?php

namespace App\Core\Listener;

use App\Core\Entity\EntityInterface;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;

class CacheDriverListener
{
    /** @var LoggerInterface */
    private $logger;

    /** @var CacheProvider */
    private $cacheProvider;

    /** @var int */
    private $lifeTime;


    public function __construct(LoggerInterface $logger, CacheProvider $cacheProvider, int $lifeTime)
    {
        $this->logger = $logger;
        $this->cacheProvider = $cacheProvider;
        $this->lifeTime = $lifeTime;
    }


    /**
     * Saves the loaded entity in the cache
     *
     * @param EntityInterface $entity The entity to save
     *
     * @ORM\PostLoad
     */
    public function saveEntityInCache(EntityInterface $entity)
    {
        $cacheId = $this->getEntityCacheId($entity);

        if ($this->cacheProvider->contains($cacheId))
        {
            return;
        }

        if ($this->cacheProvider->save($cacheId, $entity, $this->lifeTime))
        {
            $this->logger->debug("Entity [{entity}] saved in the cache with ID [{cacheId}]",
                array ("entity" => $entity, "cacheId" => $cacheId));
        }
    }


    /**
     * Evicts the flushed entity from the cache
     *
     * @param EntityInterface $entity
     *
     * @ORM\PostPersist
     * @ORM\PostUpdate
     * @ORM\PostRemove
     */
    public function evictEntityFromCache(EntityInterface $entity)
    {
        $cacheId = $this->getEntityCacheId($entity);

        if (!$this->cacheProvider->contains($cacheId))
        {
            return;
        }

        if ($this->cacheProvider->delete($cacheId))
        {
            $this->logger->debug("Entity [{entity}] with [{cacheId}] evicted from the cache",
                array ("entity" => $entity, "cacheId" => $cacheId));
        }
    }


    /**
     * Gets a unique cache identifier for the specified entity
     *
     * @param EntityInterface $entity The entity
     *
     * @return string The entity cache identifier
     */
    public function getEntityCacheId(EntityInterface $entity)
    {
        try
        {
            $reflectionClass = new \ReflectionClass($entity);
            $entityName = $reflectionClass->getShortName();
        }
        catch (\ReflectionException $e)
        {
            $entityName = get_class($entity);
        }

        return sprintf("%ss#%d", strtolower($entityName), $entity->getId());
    }

}
