<?php

namespace ColocMatching\CoreBundle\Listener;

use Psr\Log\LoggerInterface;
use ColocMatching\CoreBundle\Entity\Updatable;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

class UpdatableListener {

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }


    /**
     * Sets the timestamp to created at for an updatable object at pre persist event
     *
     * @PrePersist()
     *
     * @param Updatable $object
     */
    public function setCreatedAt(Updatable $object) {
        $this->logger->debug("Setting created at to an updatable objet", array ("object" => $object));

        $object->setCreatedAt(new \DateTime());
        $object->setLastUpdate(new \DateTime());
    }


    /**
     * Sets the timestamp to last update for an updatable object at pre update event
     *
     * @PreUpdate()
     *
     * @param Updatable $object
     */
    public function setLastUpdate(Updatable $object) {
        $this->logger->debug("Setting last update to an updatable object", array ("object" => $object));

        $object->setLastUpdate(new \DateTime());
    }

}