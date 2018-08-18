<?php

namespace App\Core\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @author Dahiorus
 */
class UserPassword extends Constraint
{

    public $message = "The user's current password must be specified";


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Validator\Constraint::getTargets()
     */
    public function getTargets()
    {
        return array (self::CLASS_CONSTRAINT);
    }

}