<?php

namespace ColocMatching\CoreBundle\Exception;

/**
 * Thrown when the creator of a group or an announcement is invalid (the creator already has a group or an
 * announcement).
 *
 * @author Dahiorus
 */
final class InvalidCreatorException extends InvalidParameterException {

    /**
     * InvalidCreatorException constructor.
     *
     * @param string $message [optional] The exception message
     */
    public function __construct(string $message = "Invalid creator") {
        parent::__construct("creator", $message);
    }

}