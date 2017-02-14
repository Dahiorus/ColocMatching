<?php

namespace ColocMatching\CoreBundle\Exception;

use ColocMatching\CoreBundle\Exception\EntityNotFoundException;

/**
 * Exception thrown when no user is found by Id
 *
 * @author Dahiorus
 */
final class UserNotFoundException extends EntityNotFoundException {


    public function __construct(int $id, \Exception $previous = null, $code = 0) {
        parent::__construct($id, sprintf("No User found with the Id %d", $id), $previous, $code);
    }

}