<?php

namespace App\Core\DTO;

/**
 * Base interface of all DTO
 *
 * @author Dahiorus
 */
interface DtoInterface
{
    /**
     * Gets the DTO identifier
     * @return integer
     */
    public function getId();


    /**
     * Returns the entity class associated with this DTO
     * @return string
     */
    public function getEntityClass() : string;
}