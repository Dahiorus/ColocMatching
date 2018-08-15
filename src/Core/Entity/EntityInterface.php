<?php

namespace App\Core\Entity;

/**
 * Interface representing a persisted entity
 *
 * @author Dahiorus
 */
interface EntityInterface
{
    /**
     * Gets the entity Id
     *
     * @return int
     */
    public function getId();


    /**
     * Sets the entity Id
     *
     * @param int $id
     */
    public function setId(?int $id);

}