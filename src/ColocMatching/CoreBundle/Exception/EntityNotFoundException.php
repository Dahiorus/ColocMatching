<?php

namespace ColocMatching\CoreBundle\Exception;

abstract class EntityNotFoundException extends ColocMatchingException {

    /**
     * Constructor
     *
     * @param string $entityName The name of the entity
     * @param string $name       The name of the attribute on which the exception would be thrown
     * @param mixed $value       The value of the attribute
     */
    public function __construct(string $entityName, string $name, $value) {
        parent::__construct("No '$entityName' found with '$name' $value", 404);
    }

}