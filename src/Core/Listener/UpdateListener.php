<?php

namespace App\Core\Listener;

use App\Core\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;

/**
 * Entity listener setting the creation date and the last update date on an entity
 *
 * @author Dahiorus
 */
class UpdateListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * Sets the creation date to the entity before persisting
     *
     * @ORM\PrePersist()
     *
     * @param AbstractEntity $entity
     *
     * @throws \Exception
     */
    public function setCreatedAt(AbstractEntity $entity)
    {
        $this->logger->debug("Setting creation date time to the entity [{entity}]", array ("entity" => $entity));

        $entity->setCreatedAt(new \DateTimeImmutable());
    }


    /**
     * Sets the last update date to the entity before updating
     *
     * @ORM\PreUpdate()
     *
     * @param AbstractEntity $entity
     */
    public function setLastUpdate(AbstractEntity $entity)
    {
        $this->logger->debug("Setting last update date time to the entity [{entity}]", array ("entity" => $entity));

        $entity->setLastUpdate(new \DateTime());
    }
}