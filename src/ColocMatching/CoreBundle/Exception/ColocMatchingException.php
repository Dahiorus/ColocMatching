<?php

namespace ColocMatching\CoreBundle\Exception;

class ColocMatchingException extends \Exception {

    /**
     * Gets the details of the exception
     * @return array
     */
    public function getDetails() : array {
        return array (
            "message" => $this->getMessage() ?: "API exception",
            "code" => $this->getCode()
        );
    }
}