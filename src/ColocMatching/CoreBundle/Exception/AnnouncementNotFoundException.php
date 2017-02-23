<?php

namespace ColocMatching\CoreBundle\Exception;

use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;

final class AnnouncementNotFoundException extends EntityNotFoundException {


    /**
     * Constructor
     *
     * @param string $name The name of the attribute on which the exception would be throw
     * @param unknown $value The value of the attribute
     * @param \Exception $previous
     * @param number $code
     */
    public function __construct(string $name, $value, \Exception $previous = null, $code = 0) {
        parent::__construct("Announcement", $name, $value, $previous, $code);
    }

}