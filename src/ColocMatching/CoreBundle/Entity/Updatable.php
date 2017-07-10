<?php

namespace ColocMatching\CoreBundle\Entity;

/**
 * Interface representing an Entity with updating information
 *
 * @author Dahiorus
 */
interface Updatable {


    /**
     * Get created at
     * @return \DateTime
     */
    public function getCreatedAt() : \DateTime;


    /**
     * Set created at
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt);


    /**
     * Get last update
     * @return \DateTime
     */
    public function getLastUpdate() : \DateTime;


    /**
     * Set last update
     *
     * @param \DateTime $lastUpdate
     */
    public function setLastUpdate(\DateTime $lastUpdate);

}