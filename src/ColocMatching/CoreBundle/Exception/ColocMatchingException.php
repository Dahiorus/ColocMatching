<?php

namespace ColocMatching\CoreBundle\Exception;

abstract class ColocMatchingException extends \Exception {

    /**
     * Converts the exception to an associative array
     * @return array
     */
    public abstract function toArray() : array;
}