<?php

namespace ColocMatching\CoreBundle\Entity;

/**
 * Interface representing a persisted entity
 *
 * @author Dahiorus
 */
interface EntityInterface {


    /**
     * Gets the entity Id
     *
     * @return int
     */
    public function getId(): int;


    /**
     * Sets the entity Id
     *
     * @param int $id
     */
    public function setId(int $id);

}