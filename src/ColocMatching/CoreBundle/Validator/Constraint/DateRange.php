<?php

namespace ColocMatching\CoreBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @author Dahiorus
 */
class DateRange extends Constraint {

    public $message = "The date range is invalid";


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Validator\Constraint::getTargets()
     */
    public function getTargets() {
        return array (self::CLASS_CONSTRAINT);
    }

}