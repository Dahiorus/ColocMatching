<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\Entity\AbstractEntity;
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
     * @param AbstractEntity $entity The entity to save
     *
     * @ORM\PostLoad
     */
    public function saveEntityInCache(AbstractEntity $entity)
    {
        $cacheId = $this->getEntityCacheId($entity);

        if ($this->cacheProvider->contains($cacheId))
        {
            return;
        }

        if ($this->cacheProvider->save($cacheId, $entity, $this->lifeTime))
        {
            $this->logger->debug("Entity saved in the cache", array ("entity" => $entity, "cacheId" => $cacheId));
        }
    }


    /**
     * Evicts the flushed entity from the cache
     *
     * @param AbstractEntity $entity
     *
     * @ORM\PostPersist
     * @ORM\PostUpdate
     * @ORM\PostRemove
     */
    public function evictEntityFromCache(AbstractEntity $entity)
    {
        $cacheId = $this->getEntityCacheId($entity);

        if (!$this->cacheProvider->contains($cacheId))
        {
            return;
        }

        if ($this->cacheProvider->delete($cacheId))
        {
            $this->logger->debug("Entity evicted from the cache", array ("entity" => $entity, "cacheId" => $cacheId));
        }
    }


    /**
     * Gets a unique cache identifier for the specified entity
     *
     * @param AbstractEntity $entity The entity
     *
     * @return string The entity cache identifier
     */
    public function getEntityCacheId(AbstractEntity $entity)
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
