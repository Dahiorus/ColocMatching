<?php

namespace ColocMatching\CoreBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Used to valid a string value is a postal address
 * @Annotation
 *
 * @author Dahiorus
 */
class AddressValue extends Constraint
{
    public $message = "This value is not a valid postal address";
}