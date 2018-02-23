<?php

namespace ColocMatching\CoreBundle\Entity;

/**
 * Interface representing an Entity with updating information
 *
 * @author Dahiorus
 */
interface Updatable extends EntityInterface
{

    /**
     * Get created at
     * @return \DateTime
     */
    public function getCreatedAt();


    /**
     * Set created at
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null);


    /**
     * Get last update
     * @return \DateTime
     */
    public function getLastUpdate();


    /**
     * Set last update
     *
     * @param \DateTime $lastUpdate
     */
    public function setLastUpdate(\DateTime $lastUpdate = null);

}