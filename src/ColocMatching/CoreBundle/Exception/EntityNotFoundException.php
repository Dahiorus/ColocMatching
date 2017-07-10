<?php

namespace ColocMatching\CoreBundle\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class EntityNotFoundException extends NotFoundHttpException {


    /**
     * Constructor
     *
     * @param string $entityName The name of the entity
     * @param string $name       The name of the attribute on which the exception would be thrown
     * @param mixed $value       The value of the attribute
     * @param \Exception $previous
     * @param int $code
     */
    public function __construct(string $entityName, string $name, $value, \Exception $previous = null, $code = 0) {
        parent::__construct("No '$entityName' found with '$name' $value", $previous, $code);
    }

}