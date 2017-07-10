<?php

namespace ColocMatching\CoreBundle\Exception;

use ColocMatching\CoreBundle\Entity\Group\Group;

final class GroupNotFoundException extends EntityNotFoundException {


    /**
     * Constructor
     *
     * @param string $name The name of the attribute on which the exception would be throw
     * @param mixed $value The value of the attribute
     * @param \Exception $previous
     * @param int $code
     */
    public function __construct(string $name, $value, \Exception $previous = null, $code = 0) {
        parent::__construct(Group::class, $name, $value, $previous, $code);
    }

}